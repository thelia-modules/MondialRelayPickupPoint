<?php
/*************************************************************************************/
/*      Copyright (c) Franck Allimant, CQFDev                                        */
/*      email : thelia@cqfdev.fr                                                     */
/*      web : http://www.cqfdev.fr                                                   */
/*                                                                                   */
/*      For the full copyright and license information, please view the LICENSE      */
/*      file that was distributed with this source code.                             */
/*************************************************************************************/

namespace MondialRelayPickupPoint\Loop;

use MondialRelayPickupPoint\Model\MondialRelayPickupPointInsuranceQuery;
use MondialRelayPickupPoint\Model\MondialRelayPickupPointPrice;
use MondialRelayPickupPoint\Model\MondialRelayPickupPointPriceQuery;
use MondialRelayPickupPoint\Model\MondialRelayPickupPointZoneConfigurationQuery;
use MondialRelayPickupPoint\MondialRelayPickupPoint;
use Propel\Runtime\ActiveQuery\Criteria;
use Thelia\Core\Template\Element\ArraySearchLoopInterface;
use Thelia\Core\Template\Element\BaseLoop;
use Thelia\Core\Template\Element\LoopResult;
use Thelia\Core\Template\Element\LoopResultRow;
use Thelia\Core\Template\Loop\Argument\Argument;
use Thelia\Core\Template\Loop\Argument\ArgumentCollection;
use Thelia\Model\AreaDeliveryModuleQuery;
use Thelia\Model\Cart;
use Thelia\Model\CountryArea;
use Thelia\Model\CountryAreaQuery;
use Thelia\Model\CountryQuery;
use Thelia\Model\ModuleQuery;
use Thelia\Model\StateQuery;
use Thelia\Type\EnumType;
use Thelia\Type\TypeCollection;

/**
 * Class Prices
 * @package MondialRelayPickupPoint\Loop
 * @method int getCountryId()
 * @method int getStateId()
 * @method string getMode()
 * @method string getInsurance()
 */
class DeliveryPrice extends BaseLoop implements ArraySearchLoopInterface
{
    /**
     * @return \Thelia\Core\Template\Loop\Argument\ArgumentCollection
     */
    protected function getArgDefinitions()
    {
        return new ArgumentCollection(
            Argument::createIntTypeArgument('country_id', null, true),
            Argument::createIntTypeArgument('state_id'),
            Argument::createBooleanTypeArgument('insurance', null, false),
            new Argument(
                'mode',
                new TypeCollection(
                    new EnumType(['home', 'relay', 'all'])
                ),
                null,
                true
            )
        );
    }

    /**
     * @return array
     * @throws \Exception
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function buildArray()
    {
        $results = [];

        if (null !== $country = CountryQuery::create()->findPk($this->getCountryId())) {
            if (null !== $stateId = $this->getStateId()) {
                $state = StateQuery::create()->findPk($this->$stateId());
            } else {
                $state = null;
            }


            // Find all areas which contains this country
            $countryInAreaList = CountryAreaQuery::findByCountryAndState($country, $state);

            $areaIdList = [];

            $module = ModuleQuery::create()->findOneByCode(MondialRelayPickupPoint::getModuleCode());

            /** @var CountryArea $countryInArea */
            foreach ($countryInAreaList as $countryInArea) {
                // Check if module is attached to the area
                if (AreaDeliveryModuleQuery::create()
                    ->filterByAreaId($countryInArea->getAreaId())
                    ->filterByModule($module)
                    ->count() > 0) {
                    $areaIdList[] = $countryInArea->getAreaId();
                }
            }

            // Find zones with the required delivery type
            $zones = MondialRelayPickupPointZoneConfigurationQuery::create()
                    ->filterByAreaId($areaIdList, Criteria::IN)
                    ->find();

            /** @var Cart $cart */
            $cart = $this->requestStack
                ->getCurrentRequest()
                ->getSession()
                ->getSessionCart($this->dispatcher)
                ;

            $cartWeight = $cart->getWeight();
            $cartValue = $cart->getTaxedAmount($country);

            /** @var MondialRelayPickupPointZoneConfigurationQuery $zone */
            foreach ($zones as $zone) {
                $result = [];

                if (null !== $deliveryPrice = MondialRelayPickupPointPriceQuery::create()
                        ->filterByAreaId($zone->getAreaId())
                        ->filterByMaxWeight($cartWeight, Criteria::GREATER_EQUAL)
                        ->orderByMaxWeight(Criteria::ASC)
                        ->findOne()) {
                    $deliveryDate = (new \DateTime())->add(new \DateInterval("P" . $zone->getDeliveryTime() . "D"));

                    // We have a price
                    $result['PRICE'] = $deliveryPrice->getPriceWithTax();
                    $result['MAX_WEIGHT'] = $deliveryPrice->getMaxWeight();
                    $result['AREA_ID'] = $deliveryPrice->getAreaId();
                    $result['DELIVERY_DELAY'] = $zone->getDeliveryTime();
                    $result['DELIVERY_DATE'] = $deliveryDate;

                    // Get insurance cost
                    if (null !== $insurance = MondialRelayPickupPointInsuranceQuery::create()
                        ->filterByMaxValue($cartValue, Criteria::GREATER_EQUAL)
                        ->orderByMaxValue(Criteria::ASC)
                        ->findOne()
                    ) {
                        $result['INSURANCE_AVAILABLE'] = true;
                        $result['INSURANCE_PRICE'] = $insurance->getPriceWithTax();
                        $result['INSURANCE_REF_VALUE'] = $insurance->getMaxValue();
                    } else {
                        $result['INSURANCE_AVAILABLE'] = false;
                    }

                    $results[] = $result;
                }
            }
        }

        return $results;
    }

    public function parseResults(LoopResult $loopResult)
    {
        /** @var MondialRelayPickupPointPrice $item */
        foreach ($loopResult->getResultDataCollection() as $item) {
            $loopResultRow = new LoopResultRow($item);

            foreach ($item as $name => $value) {
                $loopResultRow->set($name, $value);
            }

            $loopResult->addRow($loopResultRow);
        }

        return $loopResult;
    }
}
