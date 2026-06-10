<?php
/*************************************************************************************/
/*      Copyright (c) Franck Allimant, CQFDev                                        */
/*      email : thelia@cqfdev.fr                                                     */
/*      web : http://www.cqfdev.fr                                                   */
/*                                                                                   */
/*      For the full copyright and license information, please view the LICENSE      */
/*      file that was distributed with this source code.                             */
/*************************************************************************************/

namespace MondialRelayPickupPoint\Hook;

use MondialRelayPickupPoint\Form\FreeShippingForm;
use MondialRelayPickupPoint\Form\PriceAttributesUpdateForm;
use MondialRelayPickupPoint\Form\PriceCreateForm;
use MondialRelayPickupPoint\Form\PricesUpdateForm;
use MondialRelayPickupPoint\Form\SettingsForm;
use MondialRelayPickupPoint\Form\TaxRuleForm;
use MondialRelayPickupPoint\Model\MondialRelayPickupPointAreaFreeshippingQuery;
use MondialRelayPickupPoint\Model\MondialRelayPickupPointPriceQuery;
use MondialRelayPickupPoint\Model\MondialRelayPickupPointZoneConfigurationQuery;
use MondialRelayPickupPoint\MondialRelayPickupPoint;
use Propel\Runtime\ActiveQuery\Criteria;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Thelia\Core\Event\Hook\HookRenderBlockEvent;
use Thelia\Core\Event\Hook\HookRenderEvent;
use Thelia\Core\Form\TheliaFormFactory;
use Thelia\Core\Hook\BaseHook;
use Thelia\Core\Template\Parser\ParserResolver;
use Thelia\Model\AreaQuery;
use Thelia\Tools\URL;

class AdminHookManager extends BaseHook
{
    public function __construct(
        private readonly TheliaFormFactory $formFactory,
        ?EventDispatcherInterface $dispatcher = null,
        ?ParserResolver $parserResolver = null,
    ) {
        parent::__construct($dispatcher, $parserResolver);
    }

    public static function getSubscribedHooks(): array
    {
        return [
            'module.configuration' => [
                ['type' => 'back', 'method' => 'onModuleConfigure'],
            ],
            'main.top-menu-tools' => [
                ['type' => 'back', 'method' => 'onMainTopMenuTools'],
            ],
        ];
    }

    public function onModuleConfigure(HookRenderEvent $event): void
    {
        $moduleId = MondialRelayPickupPoint::getModuleId();

        $settingsView = $this->formFactory->createForm(SettingsForm::getName(), data: [
            MondialRelayPickupPoint::CODE_ENSEIGNE => MondialRelayPickupPoint::getConfigValue(MondialRelayPickupPoint::CODE_ENSEIGNE),
            MondialRelayPickupPoint::PRIVATE_KEY => MondialRelayPickupPoint::getConfigValue(MondialRelayPickupPoint::PRIVATE_KEY),
            MondialRelayPickupPoint::WEBSERVICE_URL => MondialRelayPickupPoint::getConfigValue(MondialRelayPickupPoint::WEBSERVICE_URL),
            MondialRelayPickupPoint::GOOGLE_MAPS_API_KEY => MondialRelayPickupPoint::getConfigValue(MondialRelayPickupPoint::GOOGLE_MAPS_API_KEY),
            MondialRelayPickupPoint::ALLOW_INSURANCE => (bool) MondialRelayPickupPoint::getConfigValue(MondialRelayPickupPoint::ALLOW_INSURANCE),
        ])->createView()->getView();
        $taxRuleView = $this->formFactory->createForm(TaxRuleForm::getName())->createView()->getView();

        $freeShippingActive = MondialRelayPickupPoint::getConfigValue('mondial_relay_pickup_point_free_shipping_active');
        $freeShippingView = $this->formFactory->createForm(
            FreeShippingForm::getName(),
            data: ['freeshipping' => (bool) $freeShippingActive]
        )->createView()->getView();

        $event->add(
            $this->render('MondialRelayPickupPoint/module-configuration.html.twig', [
                'settings_form' => $settingsView,
                'tax_rule_form' => $taxRuleView,
                'freeshipping_form' => $freeShippingView,
                'prices_update_form' => $this->formFactory->createForm(PricesUpdateForm::getName())->createView()->getView(),
                'price_create_form' => $this->formFactory->createForm(PriceCreateForm::getName())->createView()->getView(),
                'area_attributes_form' => $this->formFactory->createForm(PriceAttributesUpdateForm::getName())->createView()->getView(),
                'module_id' => $moduleId,
                'free_shipping_active' => (bool) $freeShippingActive,
                'currency_symbol' => $this->getDefaultCurrencySymbol(),
                'areas' => $this->getAreas($moduleId),
            ])
        );
    }

    public function onMainTopMenuTools(HookRenderBlockEvent $event): void
    {
        $event->add(
            [
                'id' => 'tools_mondial_relay',
                'class' => '',
                'url' => URL::getInstance()->absoluteUrl('/admin/module/MondialRelayPickupPoint'),
                'title' => $this->trans('Mondial Relay pickup point', [], MondialRelayPickupPoint::DOMAIN_NAME),
            ]
        );
    }

    /**
     * Reproduces {loop type="area"} + nested prices / area-attributes / area-freeshipping loops in PHP.
     *
     * @return array<int, array<string, mixed>>
     */
    private function getAreas(int $moduleId): array
    {
        $areas = AreaQuery::create()
            ->useAreaDeliveryModuleQuery()
                ->filterByDeliveryModuleId([$moduleId], Criteria::IN)
            ->endUse()
            ->orderById()
            ->find();

        $result = [];

        foreach ($areas as $area) {
            $areaId = $area->getId();

            $prices = [];
            $priceRows = MondialRelayPickupPointPriceQuery::create()
                ->filterByAreaId($areaId)
                ->orderByMaxWeight()
                ->find();
            foreach ($priceRows as $price) {
                $prices[] = [
                    'id' => $price->getId(),
                    'max_weight' => $price->getMaxWeight(),
                    'price' => $price->getPriceWithTax(),
                ];
            }

            $zoneConfig = MondialRelayPickupPointZoneConfigurationQuery::create()->findOneByAreaId($areaId);
            $freeShipping = MondialRelayPickupPointAreaFreeshippingQuery::create()->findOneByAreaId($areaId);

            $result[] = [
                'id' => $areaId,
                'name' => $area->getName(),
                'prices' => $prices,
                'delivery_time' => $zoneConfig?->getDeliveryTime(),
                'free_shipping_amount' => $freeShipping?->getCartAmount(),
            ];
        }

        return $result;
    }

    private function getDefaultCurrencySymbol(): string
    {
        $currency = \Thelia\Model\CurrencyQuery::create()->findOneByByDefault(true);

        return $currency?->getSymbol() ?? '';
    }
}
