<?php

namespace MondialRelayPickupPoint\Api\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use MondialRelayPickupPoint\Api\Resource\MondialRelayPickupPointDeliveryPriceResource;
use MondialRelayPickupPoint\Model\MondialRelayPickupPointInsuranceQuery;
use MondialRelayPickupPoint\Model\MondialRelayPickupPointPriceQuery;
use MondialRelayPickupPoint\Model\MondialRelayPickupPointZoneConfigurationQuery;
use MondialRelayPickupPoint\MondialRelayPickupPoint;
use Propel\Runtime\ActiveQuery\Criteria;
use Symfony\Component\HttpFoundation\RequestStack;
use Thelia\Model\AreaDeliveryModuleQuery;
use Thelia\Model\CountryAreaQuery;
use Thelia\Model\CountryQuery;
use Thelia\Model\ModuleQuery;
use Thelia\Model\StateQuery;

class MondialRelayPickupPointDeliveryPriceProvider implements ProviderInterface
{
    /**
     * @param RequestStack $requestStack
     */
    public function __construct(
        private RequestStack $requestStack
    ) {}

    /**
     * @param Operation $operation
     * @param array $uriVariables
     * @param array $context
     * @return object|array|object[]|null
     * @throws \DateMalformedIntervalStringException
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $request = $this->requestStack->getCurrentRequest();
        $countryId = $request?->query->get('country_id');
        $stateId = $request?->query->get('state_id');
        $insurance = $request?->query->getBoolean('insurance', false);
        $mode = $request?->query->get('mode', 'relay');

        $results = [];

        if ($countryId && null !== $country = CountryQuery::create()->findPk($countryId)) {
            $state = null;
            if ($stateId) {
                $state = StateQuery::create()->findPk($stateId);
            }

            $countryInAreaList = CountryAreaQuery::findByCountryAndState($country, $state);

            $areaIdList = [];

            $module = ModuleQuery::create()->findOneByCode(MondialRelayPickupPoint::getModuleCode());

            foreach ($countryInAreaList as $countryInArea) {
                if (AreaDeliveryModuleQuery::create()
                        ->filterByAreaId($countryInArea->getAreaId())
                        ->filterByModule($module)
                        ->count() > 0) {
                    $areaIdList[] = $countryInArea->getAreaId();
                }
            }

            $zones = MondialRelayPickupPointZoneConfigurationQuery::create()
                ->filterByAreaId($areaIdList, Criteria::IN)
                ->find();

            $cart = $request->getSession()->getSessionCart();
            $cartWeight = $cart->getWeight();
            $cartValue = $cart->getTaxedAmount($country);

            foreach ($zones as $zone) {
                if (null !== $deliveryPrice = MondialRelayPickupPointPriceQuery::create()
                        ->filterByAreaId($zone->getAreaId())
                        ->filterByMaxWeight($cartWeight, Criteria::GREATER_EQUAL)
                        ->orderByMaxWeight(Criteria::ASC)
                        ->findOne()) {

                    $resource = new MondialRelayPickupPointDeliveryPriceResource();
                    $resource->price = $deliveryPrice->getPriceWithTax();
                    $resource->maxWeight = $deliveryPrice->getMaxWeight();
                    $resource->areaId = $deliveryPrice->getAreaId();
                    $resource->deliveryDelay = $zone->getDeliveryTime();
                    $resource->deliveryDate = (new \DateTime())->add(new \DateInterval("P" . $zone->getDeliveryTime() . "D"));

                    if (null !== $insurance = MondialRelayPickupPointInsuranceQuery::create()
                            ->filterByMaxValue($cartValue, Criteria::GREATER_EQUAL)
                            ->orderByMaxValue(Criteria::ASC)
                            ->findOne()
                    ) {
                        $resource->insuranceAvailable = true;
                        $resource->insurancePrice = $insurance->getPriceWithTax();
                        $resource->insuranceRefValue = $insurance->getMaxValue();
                    } else {
                        $resource->insuranceAvailable = false;
                    }

                    $results[] = $resource;
                }
            }
        }

        return $results;
    }
}
