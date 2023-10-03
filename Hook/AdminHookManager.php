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

use MondialRelayPickupPoint\MondialRelayPickupPoint;
use Thelia\Core\Event\Hook\HookRenderBlockEvent;
use Thelia\Core\Event\Hook\HookRenderEvent;
use Thelia\Core\Hook\BaseHook;
use Thelia\Tools\URL;

class AdminHookManager extends BaseHook
{
    public function onModuleConfigure(HookRenderEvent $event)
    {
        $vars = [
            'code_enseigne' => MondialRelayPickupPoint::getConfigValue(MondialRelayPickupPoint::CODE_ENSEIGNE),
            'private_key' =>  MondialRelayPickupPoint::getConfigValue(MondialRelayPickupPoint::PRIVATE_KEY),
            'allow_relay_delivery' =>  MondialRelayPickupPoint::getConfigValue(MondialRelayPickupPoint::ALLOW_RELAY_DELIVERY),
            'allow_home_delivery' =>  MondialRelayPickupPoint::getConfigValue(MondialRelayPickupPoint::ALLOW_HOME_DELIVERY),
            'allow_insurance' =>  MondialRelayPickupPoint::getConfigValue(MondialRelayPickupPoint::ALLOW_INSURANCE),
            "mondial_relay_pickup_point_free_shipping_active" => MondialRelayPickupPoint::getConfigValue("mondial_relay_pickup_point_free_shipping_active"),

            'module_id' =>  MondialRelayPickupPoint::getModuleId()
        ];

        $event->add(
            $this->render('mondialrelaypickuppoint/module-configuration.html', $vars)
        );
    }

    public function onMainTopMenuTools(HookRenderBlockEvent $event)
    {
        $event->add(
            [
                'id' => 'tools_mondial_relay',
                'class' => '',
                'url' => URL::getInstance()->absoluteUrl('/admin/module/MondialRelayPickupPoint'),
                'title' => $this->trans('Mondial Relay pickup point', [], MondialRelayPickupPoint::DOMAIN_NAME)
            ]
        );
    }

    public function onModuleConfigureJs(HookRenderEvent $event)
    {
        $event
            ->add($this->render("mondialrelaypickuppoint/assets/js/mondialrelay.js.html"))
            ->add($this->addJS("mondialrelaypickuppoint/assets/js/bootstrap-notify.min.js"))
        ;
    }
}
