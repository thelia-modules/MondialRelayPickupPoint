<?php

namespace MondialRelayPickupPoint\Loop;

use MondialRelayPickupPoint\Model\MondialRelayPickupPointFreeshipping;
use MondialRelayPickupPoint\Model\MondialRelayPickupPointFreeshippingQuery;
use Thelia\Core\Template\Element\BaseLoop;
use Propel\Runtime\ActiveQuery\ModelCriteria;
use Thelia\Core\Template\Element\LoopResult;
use Thelia\Core\Template\Element\LoopResultRow;
use Thelia\Core\Template\Element\PropelSearchLoopInterface;
use Thelia\Core\Template\Loop\Argument\Argument;
use Thelia\Core\Template\Loop\Argument\ArgumentCollection;

class FreeShipping extends BaseLoop implements PropelSearchLoopInterface
{
    /**
     * @return ArgumentCollection
     */
    protected function getArgDefinitions(): ArgumentCollection
    {
        return new ArgumentCollection(
            Argument::createIntTypeArgument('id')
        );
    }

    public function buildModelCriteria(): ModelCriteria
    {
        if (null === $isFreeShippingActive = MondialRelayPickupPointFreeshippingQuery::create()->findOneById(1)){
            $isFreeShippingActive = new MondialRelayPickupPointFreeshipping();
            $isFreeShippingActive->setId(1);
            $isFreeShippingActive->setActive(0);
            $isFreeShippingActive->save();
        }

        return MondialRelayPickupPointFreeshippingQuery::create()->filterById(1);
    }

    public function parseResults(LoopResult $loopResult): LoopResult
    {
        /** @var MondialRelayPickupPointFreeshipping $freeshipping */
        foreach ($loopResult->getResultDataCollection() as $freeshipping) {
            $loopResultRow = new LoopResultRow($freeshipping);
            $loopResultRow
                ->set('FREESHIPPING_ACTIVE', $freeshipping->getActive())
                ->set('FREESHIPPING_FROM', $freeshipping->getFreeshippingFrom());
            $loopResult->addRow($loopResultRow);
        }
        return $loopResult;
    }

}