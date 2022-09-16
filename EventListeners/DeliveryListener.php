<?php
/*************************************************************************************/
/*      Copyright (c) Franck Allimant, CQFDev                                        */
/*      email : thelia@cqfdev.fr                                                     */
/*      web : http://www.cqfdev.fr                                                   */
/*                                                                                   */
/*      For the full copyright and license information, please view the LICENSE      */
/*      file that was distributed with this source code.                             */
/*************************************************************************************/

namespace MondialRelayPickupPoint\EventListeners;

use MondialRelay\BussinessHours\BussinessHours;
use MondialRelay\Point\PointFactory;
use MondialRelayPickupPoint\Event\FindRelayEvent;
use MondialRelayPickupPoint\Event\MondialRelayEvents;
use MondialRelayPickupPoint\Model\MondialRelayPickupPointAddress;
use MondialRelayPickupPoint\Model\MondialRelayPickupPointAddressQuery;
use MondialRelayPickupPoint\MondialRelayPickupPoint;
use MondialRelay\Point\Point;
use MondialRelayPickupPoint\WebApi\ApiHelper;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Thelia\Action\BaseAction;
use Thelia\Core\Event\Order\OrderEvent;
use Thelia\Core\Event\TheliaEvents;
use Thelia\Core\HttpFoundation\Request;
use Thelia\Core\HttpFoundation\Session\Session;
use Thelia\Core\Translation\Translator;
use Thelia\Model\CountryQuery;
use Thelia\Model\OrderAddressQuery;
use MondialRelayPickupPoint\WebApi\MondialRelayWebApi;

class DeliveryListener extends BaseAction implements EventSubscriberInterface
{
    /** @var RequestStack */
    protected $requestStack;
    private MondialRelayWebAPI $mondialRelayWebApi;

    /**
     * DeliveryPostageListener constructor.
     * @param RequestStack $requestStack
     */
    public function __construct(RequestStack $requestStack, MondialRelayWebApi $mondialRelayWebApi)
    {
        $this->requestStack = $requestStack;
        $this->mondialRelayWebApi = $mondialRelayWebApi;
    }

    protected function makeHoraire($str)
    {
        return substr($str, 0, 2) . ':' . substr($str, 2);
    }

    /**
     * Update the order delivery address with MondialRelayPickupPoint point data
     *
     * @param OrderEvent $event
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function updateOrderDeliveryAddress(OrderEvent $event)
    {
        /** @var Session $session */
        $session = $this->requestStack->getCurrentRequest()->getSession();

        if (null !== $mrAddressId = $session->get(MondialRelayPickupPoint::SESSION_SELECTED_PICKUP_RELAY_ID)) {
            if (null !== $mrRelayPickup = MondialRelayPickupPointAddressQuery::create()->findPk($mrAddressId)) {
                if (false !== $relayData = json_decode($mrRelayPickup->getJsonRelayData(), true)) {
                    if (null !== $orderAddress = OrderAddressQuery::create()->findPK($event->getOrder()->getDeliveryOrderAddressId())) {
                        $orderAddress
                            ->setCompany($relayData['name'])
                            ->setFirstname(
                                Translator::getInstance()->trans(
                                    "Pickup relay #%number",
                                    ['%number' => $relayData['id']],
                                    MondialRelayPickupPoint::DOMAIN_NAME
                                )
                            )
                            ->setLastname('')
                            ->setAddress1($relayData['address'])
                            ->setAddress2('')
                            ->setAddress3('')
                            ->setZipcode($relayData['zipcode'])
                            ->setCity($relayData['city'])
                            ->setCountry(CountryQuery::create()->findOneByIsoalpha2($relayData['country']))
                            ->save();

                        $mrRelayPickup
                            ->setOrderAddressId($orderAddress->getId())
                            ->save();
                    }
                }
            }
        }
    }

    /**
     * @param OrderEvent $event
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function updateCurrentDeliveryAddress(OrderEvent $event, $eventName, EventDispatcherInterface $dispatcher)
    {
        /** @var Request $request */
        $request = $this->requestStack->getCurrentRequest();

        /** @var Session $session */
        $session = $request->getSession();

        // Reset stored pickup address, if any
        if (null !== $mrAddressId = $session->remove(MondialRelayPickupPoint::SESSION_SELECTED_PICKUP_RELAY_ID)) {
            // Do not delete, as the customer may have do a back, and restart another order
            // MondialRelayPickupPointPickupAddressQuery::create()->filterById($mrAddressId)->delete();
        }

        if ($event->getDeliveryModule() == MondialRelayPickupPoint::getModuleId()) {

            // Get the selected pickup relay
            if (null !== $relayId = $request->get('MondialRelayPickupPoint_relay', null)) {
                $countryId = $request->get('mondial_relay_country_id', 0);

                // Load pickup data for the selected point
                $relayDataEvent = new FindRelayEvent($countryId, '', '', 0);
                $relayDataEvent->setNumPointRelais($relayId);

                $dispatcher->dispatch($relayDataEvent, MondialRelayEvents::FIND_RELAYS);

                // We're supposed to get only one point
                $points = $relayDataEvent->getPoints();

                if (isset($points[0])) {
                    // Create a new record to store the pickup data
                    $pickupAddress = new MondialRelayPickupPointAddress();
                    $pickupAddress
                        ->setJsonRelayData(json_encode($points[0]))
                        ->save();

                    $session->set(MondialRelayPickupPoint::SESSION_SELECTED_PICKUP_RELAY_ID, $pickupAddress->getId());
                }
            }
        }
    }

    public function findRelays(FindRelayEvent $event, $eventName, EventDispatcherInterface $dispatcher)
    {
        $days = [
            'monday' => Translator::getInstance()->trans("Monday"),
            'tuesday' => Translator::getInstance()->trans("Tuesday"),
            'wednesday' => Translator::getInstance()->trans("Wednesday"),
            'thursday' => Translator::getInstance()->trans("Thursday"),
            'friday' => Translator::getInstance()->trans("Friday"),
            'saturday' => Translator::getInstance()->trans("Saturday"),
            'sunday' => Translator::getInstance()->trans("Sunday")
        ];

        if (null !== $country = CountryQuery::create()->findPk($event->getCountryId())) {

            $cartWeightInGrammes = 1000 * $this->requestStack
                    ->getCurrentRequest()
                    ->getSession()
                    ->getSessionCart($dispatcher)
                    ->getWeight();

            try {
                $this->mondialRelayWebApi->_Api_CustomerCode =  MondialRelayPickupPoint::getConfigValue(MondialRelayPickupPoint::CODE_ENSEIGNE);
                $this->mondialRelayWebApi->_Api_BrandId = "11";
                $this->mondialRelayWebApi->_Api_SecretKey =  MondialRelayPickupPoint::getConfigValue(MondialRelayPickupPoint::PRIVATE_KEY);
                $this->mondialRelayWebApi->_Api_Version = "2.0";

                $result = $this->mondialRelayWebApi->SearchParcelShop(
                    strtoupper($country->getIsoalpha2()),
                    $event->getZipcode(),
                    "",
                    $cartWeightInGrammes
                );

                $points = [];

                $pointFactory = new PointFactory();
                $this->checkResponse('WSI3_PointRelais_Recherche', $result);

                foreach ($result['WSI3_PointRelais_RechercheResult']['PointsRelais']['PointRelais_Details'] as $destination_point) {
                    foreach ($destination_point as $key => $value) {
                        if (!is_array($value)) {
                            continue;
                        }
                        $destination_point[$key] = (object) $value;
                    }

                    $points[] = $pointFactory->create((object)$destination_point);
                }

            } catch (\Exception $ex) {
                $points = [];

                $event->setError($ex->getMessage());
            }

            $normalizedPoints = [];

            /** @var Point $point */
            foreach ($points as $point) {
                $normalizedPoint = [
                    'id' => $point->id(),
                    'latitude' => $point->latitude(),
                    'longitude' => $point->longitude(),
                    'zipcode' => $point->cp(),
                    'city' => $point->city(),
                    'country' => $point->country()
                ];

                $addresses = $point->address();

                $nom = $addresses[0];
                if (!empty($adresses[1])) {
                    $nom .= '<br> ' . $addresses[1];
                }

                $normalizedPoint["name"] = $nom;

                $address = $addresses[2];
                if (!empty($adresses[3])) {
                    $address .= '<br> ' . $addresses[3];
                }

                $normalizedPoint["address"] = $address;


                $horaires = [];

                /** @var BussinessHours $horaire */
                foreach ($point->business_hours() as $horaire) {
                    if ($horaire->openingTime1() != '0000' && $horaire->openingTime2() !== '0000') {
                        $data = ['day' => $days[$horaire->day()]];

                        $o1 = $horaire->openingTime1();
                        $o2 = $horaire->openingTime2();

                        if (!empty($o1) && $o1 != '0000') {
                            $data['opening_time_1'] = $this->makeHoraire($horaire->openingTime1());
                            $data['closing_time_1'] = $this->makeHoraire($horaire->closingTime1());
                        }

                        if (!empty($o2) && $o2 != '0000') {
                            $data['opening_time_2'] = $this->makeHoraire($horaire->openingTime2());
                            $data['closing_time_2'] = $this->makeHoraire($horaire->closingTime2());
                        }

                        $horaires[] = $data;
                    }
                }

                $normalizedPoint["openings"] = $horaires;

                $normalizedPoints[] = $normalizedPoint;
            }

            $event->setPoints($normalizedPoints);
        }
    }

    private function checkResponse($method, $result)
    {
        $method = $method . "Result";
        if ($result[$method]['STAT'] != 0) {
            throw new \InvalidArgumentException(sprintf('Error getting pmondial relai points : %s', ApiHelper::GetStatusCode($result[$method]['STAT'])));
        }
    }

    /**
     * Clear stored information once the order has been processed.
     *
     * @param OrderEvent $event
     * @param $eventName
     * @param EventDispatcherInterface $dispatcher
     */
    public function clearDeliveryData(OrderEvent $event, $eventName, EventDispatcherInterface $dispatcher)
    {
        $session = $this->requestStack->getCurrentRequest()->getSession();

        // Clear the session context
        $session->remove(MondialRelayPickupPoint::SESSION_SELECTED_DELIVERY_TYPE);
        $session->remove(MondialRelayPickupPoint::SESSION_SELECTED_PICKUP_RELAY_ID);
    }

    public static function getSubscribedEvents()
    {
        return [
            TheliaEvents::ORDER_SET_DELIVERY_MODULE => ['updateCurrentDeliveryAddress', 64],
            TheliaEvents::ORDER_BEFORE_PAYMENT => ['updateOrderDeliveryAddress', 256],
            TheliaEvents::ORDER_CART_CLEAR => ['clearDeliveryData', 256],

            MondialRelayEvents::FIND_RELAYS => ["findRelays", 128]
        ];
    }
}
