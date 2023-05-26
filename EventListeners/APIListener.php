<?php

namespace MondialRelayPickupPoint\EventListeners;

use ColissimoLabel\Exception\Exception;
use ColissimoPickupPoint\WebService\FindByAddress;
use InvalidArgumentException;
use MondialRelayPickupPoint\Event\FindRelayEvent;
use MondialRelayPickupPoint\Event\MondialRelayEvents;
use MondialRelayPickupPoint\Model\MondialRelayPickupPointZoneConfigurationQuery;
use MondialRelayPickupPoint\MondialRelayPickupPoint;
use OpenApi\Events\DeliveryModuleOptionEvent;
use OpenApi\Events\OpenApiEvents;
use OpenApi\Model\Api\DeliveryModuleOption;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Thelia\Core\Event\Delivery\PickupLocationEvent;
use Thelia\Core\Event\TheliaEvents;
use Thelia\Core\Translation\Translator;
use Thelia\Model\Area;
use Thelia\Model\Base\CountryQuery;
use Thelia\Model\PickupLocation;
use Thelia\Model\PickupLocationAddress;
use Thelia\Module\Exception\DeliveryException;

class APIListener implements EventSubscriberInterface
{
    /** @var ContainerInterface */
    protected $container;


    /** @var RequestStack */
    protected $requestStack;

    /** @var EventDispatcherInterface */
    protected $eventDispatcher;

    /**
     * APIListener constructor.
     * @param ContainerInterface $container We need the container because we use a service from another module
     * which is not mandatory, and using its service without it being installed will crash
     */
    public function __construct(ContainerInterface $container, RequestStack $requestStack, EventDispatcherInterface $eventDispatcher)
    {
        $this->container = $container;
        $this->requestStack = $requestStack;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function getDeliveryModuleOptions(DeliveryModuleOptionEvent $deliveryModuleOptionEvent)
    {
        if ($deliveryModuleOptionEvent->getModule()->getId() !== MondialRelayPickupPoint::getModuleId()) {
            return;
        }

        $isValid = true;

        $module = new MondialRelayPickupPoint();
        $country = $deliveryModuleOptionEvent->getCountry();
        $minimumDeliveryDate = null;
        $orderPostage = null;

        $locale = $this->requestStack->getCurrentRequest()->getSession()->getLang()->getLocale();

        if (empty($countryAreas = $module->getAreaForCountry($country))) {
            throw new DeliveryException(Translator::getInstance()->trans("Your delivery country is not covered by Mondial Relay"));
        }


        /** @var Area $countryArea */
        foreach ($countryAreas as $area) {
            $orderPostage = $module->getMinPostage(
                $country,
                $this->requestStack->getCurrentRequest()->getSession()->getLang()->getLocale(),
                $deliveryModuleOptionEvent->getCart()->getWeight(),
                $deliveryModuleOptionEvent->getCart()->getTaxedAmount($country)
            );
            $areaConfiguration = MondialRelayPickupPointZoneConfigurationQuery::create()->filterByAreaId($area->getId())->findOne();

            $minimumDeliveryDate = $areaConfiguration ? (new \DateTime())->add(new \DateInterval('P' . $areaConfiguration->getDeliveryTime() . 'D')) : null;
        }

        /** @var DeliveryModuleOption $deliveryModuleOption */
        $deliveryModuleOption = ($this->container->get('open_api.model.factory'))->buildModel('DeliveryModuleOption');
        $deliveryModuleOption
            ->setCode('MondialRelayPickupPoint')
            ->setValid($isValid)
            ->setTitle($deliveryModuleOptionEvent->getModule()->setLocale($locale)->getTitle())
            ->setImage('')
            ->setMinimumDeliveryDate(($minimumDeliveryDate) ? $minimumDeliveryDate->format('d/m/Y') : null)
            ->setMaximumDeliveryDate(null)
            ->setPostage(($orderPostage) ? $orderPostage->getAmount() : 0)
            ->setPostageTax(($orderPostage) ? $orderPostage->getAmountTax() : 0)
            ->setPostageUntaxed(($orderPostage) ? $orderPostage->getAmount() - $orderPostage->getAmountTax() : 0);

        $deliveryModuleOptionEvent->appendDeliveryModuleOptions($deliveryModuleOption);
    }

    public function getPickupLocations(PickupLocationEvent $pickupLocationEvent)
    {
        if (null !== $moduleIds = $pickupLocationEvent->getModuleIds()) {
            if (!in_array(MondialRelayPickupPoint::getModuleId(), $moduleIds)) {
                return;
            }
        }

        if (null === $country = $pickupLocationEvent->getCountry()) {
            $country = CountryQuery::create()->filterByByDefault(1)->findOne();
        }

        $findRelayEvent = new FindRelayEvent(
            $country->getId(),
            $pickupLocationEvent->getCity(),
            $pickupLocationEvent->getZipCode(),
            (float)($pickupLocationEvent->getRadius() ?: 10)
        );

        $this->eventDispatcher->dispatch($findRelayEvent, MondialRelayEvents::FIND_RELAYS);

        foreach ($findRelayEvent->getPoints() as $point) {
            $pickupLocationEvent->appendLocation($this->createPickupLocationFromResponse($point));
        }
    }

    protected function createPickupLocationFromResponse(array $point)
    {
        $pickupLocation = new PickupLocation();

        $pickupLocation
            ->setId($point['id'])
            ->setTitle($point['name'])
            ->setAddress($this->createPickupLocationAddressFromResponse($point))
            ->setLatitude($point['latitude'])
            ->setLongitude($point['longitude'])
            ->setModuleId(MondialRelayPickupPoint::getModuleId());

        $pickupLocation = $this->setOpeningHours($pickupLocation, $point['openings']);

        return $pickupLocation;
    }

    public function setOpeningHours(PickupLocation $pickupLocation, $openings)
    {
        foreach ($openings as $opening) {
            if (!isset($opening['opening_time_1'])) {
                continue;
            }

            $openingDay = '';
            switch ($opening['day']) {
                case 'Monday':
                    $openingDay = PickupLocation::MONDAY_OPENING_HOURS_KEY;
                    break;
                case 'Tuesday':
                    $openingDay = PickupLocation::TUESDAY_OPENING_HOURS_KEY;
                    break;
                case 'Wednesday':
                    $openingDay = PickupLocation::WEDNESDAY_OPENING_HOURS_KEY;
                    break;
                case 'Thursday':
                    $openingDay = PickupLocation::THURSDAY_OPENING_HOURS_KEY;
                    break;
                case 'Friday':
                    $openingDay = PickupLocation::FRIDAY_OPENING_HOURS_KEY;
                    break;
                case 'Saturday':
                    $openingDay = PickupLocation::SATURDAY_OPENING_HOURS_KEY;
                    break;
                case 'Sunday':
                    $openingDay = PickupLocation::SUNDAY_OPENING_HOURS_KEY;
                    break;
            }

            $openTime1 = isset($opening['opening_time_1']) ? $opening['opening_time_1'] : '';
            $closeTime1 = isset($opening['closing_time_1']) ? $opening['closing_time_1'] : '';
            $openTime2 = isset($opening['opening_time_2']) ? $opening['opening_time_2'] : '';
            $closeTime2 = isset($opening['closing_time_2']) ? $opening['closing_time_2'] : '';

            $pickupLocation->setOpeningHours(
                $openingDay,
                $openTime1 . ' ' . $closeTime1 . ' ' . $openTime2 . ' ' . $closeTime2
            );
        }
        return $pickupLocation;
    }

    protected function createPickupLocationAddressFromResponse($point)
    {
        /** We create the new location address */
        $pickupLocationAddress = new PickupLocationAddress();

        $pickupLocationAddress
            ->setId($point['id'])
            ->setTitle($point['name'])
            ->setAddress1($point['address'])
            ->setAddress2('')
            ->setAddress3('')
            ->setCity($point['city'])
            ->setZipCode($point['zipcode'])
            ->setPhoneNumber('')
            ->setCellphoneNumber('')
            ->setCompany($point['name'])
            ->setCountryCode($point['country'])
            ->setFirstName('')
            ->setLastName('')
            ->setIsDefault(0)
            ->setLabel('')
            ->setAdditionalData([]);

        return $pickupLocationAddress;
    }

    public static function getSubscribedEvents()
    {
        $listenedEvents = [];

        /** Check for old versions of Thelia where the events used by the API didn't exists */
        if (class_exists(DeliveryModuleOptionEvent::class)) {
            $listenedEvents[OpenApiEvents::MODULE_DELIVERY_GET_OPTIONS] = array("getDeliveryModuleOptions", 129);
        }

        if (class_exists(PickupLocation::class)) {
            $listenedEvents[TheliaEvents::MODULE_DELIVERY_GET_PICKUP_LOCATIONS] = array("getPickupLocations", 128);
        }

        return $listenedEvents;
    }
}
