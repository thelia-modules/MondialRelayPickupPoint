<?php


namespace MondialRelayPickupPoint\Loop;


use MondialRelayPickupPoint\Model\MondialRelayPickupPointAreaFreeshipping;
use MondialRelayPickupPoint\Model\MondialRelayPickupPointAreaFreeshippingQuery;
use Thelia\Core\Template\Element\BaseLoop;
use Propel\Runtime\ActiveQuery\ModelCriteria;
use Thelia\Core\Template\Element\LoopResult;
use Thelia\Core\Template\Element\LoopResultRow;
use Thelia\Core\Template\Element\PropelSearchLoopInterface;
use Thelia\Core\Template\Loop\Argument\Argument;
use Thelia\Core\Template\Loop\Argument\ArgumentCollection;

class AreaFreeShipping extends BaseLoop implements PropelSearchLoopInterface
{
    /**
     * @return ArgumentCollection
     */
    protected function getArgDefinitions(): ArgumentCollection
    {
        return new ArgumentCollection(
            Argument::createIntTypeArgument('area_id')
        );
    }

    public function buildModelCriteria(): ModelCriteria
    {
        $areaId = $this->getAreaId();

        $search = MondialRelayPickupPointAreaFreeshippingQuery::create();

        if (null !== $areaId) {
            $search->filterByAreaId($areaId);
        }

        return $search;
    }

    public function parseResults(LoopResult $loopResult): LoopResult
    {
        /** @var MondialRelayPickupPointAreaFreeshipping $mode */
        foreach ($loopResult->getResultDataCollection() as $mode) {
            $loopResultRow = new LoopResultRow($mode);
            $loopResultRow
                ->set("ID", $mode->getId())
                ->set("AREA_ID", $mode->getAreaId())
                ->set("CART_AMOUNT", $mode->getCartAmount());
            $loopResult->addRow($loopResultRow);
        }
        return $loopResult;
    }
}