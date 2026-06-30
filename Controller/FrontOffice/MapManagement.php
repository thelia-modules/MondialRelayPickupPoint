<?php
/*************************************************************************************/
/*      Copyright (c) Franck Allimant, CQFDev                                        */
/*      email : thelia@cqfdev.fr                                                     */
/*      web : http://www.cqfdev.fr                                                   */
/*                                                                                   */
/*      For the full copyright and license information, please view the LICENSE      */
/*      file that was distributed with this source code.                             */
/*************************************************************************************/

/**
 * Created by Franck Allimant, CQFDev <franck@cqfdev.fr>
 * Date: 12/03/2018 10:41
 */

namespace MondialRelayPickupPoint\Controller\FrontOffice;

use MondialRelayPickupPoint\Event\FindRelayEvent;
use MondialRelayPickupPoint\Event\MondialRelayEvents;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Thelia\Controller\Front\BaseFrontController;
use Thelia\Core\HttpFoundation\JsonResponse;

class MapManagement extends BaseFrontController
{
    public function getRelayMapAction(EventDispatcherInterface $eventDispatcher, Request $request)
    {
        $event = new FindRelayEvent(
            intval($request->attributes->get('country_id', $request->query->get('country_id', $request->request->get('country_id', 0)))),
            $request->attributes->get('city', $request->query->get('city', $request->request->get('city', ''))),
            $request->attributes->get('zipcode', $request->query->get('zipcode', $request->request->get('zipcode', ''))),
            floatval($request->attributes->get('radius', $request->query->get('radius', $request->request->get('radius', 10))))
        );

        $eventDispatcher->dispatch($event, MondialRelayEvents::FIND_RELAYS);


        return new JsonResponse([
            'points' => $event->getPoints(),
            'error' => $event->getError()
        ]);
    }
}
