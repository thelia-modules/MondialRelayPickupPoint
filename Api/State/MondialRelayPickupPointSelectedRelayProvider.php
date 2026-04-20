<?php

namespace MondialRelayPickupPoint\Api\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use ApiPlatform\Metadata\GetCollection;
use MondialRelayPickupPoint\Api\Resource\MondialRelayPickupPointSelectedRelayResource;
use MondialRelayPickupPoint\Model\MondialRelayPickupPointAddressQuery;
use MondialRelayPickupPoint\MondialRelayPickupPoint;
use Symfony\Component\HttpFoundation\RequestStack;
use Thelia\Model\OrderQuery;

class MondialRelayPickupPointSelectedRelayProvider implements ProviderInterface
{
    public function __construct(private RequestStack $requestStack) {}

    /**
     * @param Operation $operation
     * @param array $uriVariables
     * @param array $context
     * @return array|object|object[]|null
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array|object|null
    {
        $request = $this->requestStack->getCurrentRequest();
        $session = $request?->getSession();

        if ($operation instanceof GetCollection || $operation->getName() === 'api_mondial_relay_pickup_point_selected_relay_get_collection_front') {
            $resources = [];
            if ($session && $session->has(MondialRelayPickupPoint::SESSION_SELECTED_PICKUP_RELAY_ID)) {
                $relayId = $session->get(MondialRelayPickupPoint::SESSION_SELECTED_PICKUP_RELAY_ID);
                $relay = MondialRelayPickupPointAddressQuery::create()->findPk($relayId);
                if ($relay) {
                    $resource = $this->mapToResource($relay);
                    if ($resource !== null) {
                        $resources[] = $resource;
                    }
                }
            }
            return $resources;
        }

        if (isset($uriVariables['id'])) {
            $relay = MondialRelayPickupPointAddressQuery::create()->findPk($uriVariables['id']);
            if ($relay) {
                return $this->mapToResource($relay);
            }
        }

        if ($session && $session->has(MondialRelayPickupPoint::SESSION_SELECTED_PICKUP_RELAY_ID)) {
            $relayId = $session->get(MondialRelayPickupPoint::SESSION_SELECTED_PICKUP_RELAY_ID);
            $relay = MondialRelayPickupPointAddressQuery::create()->findPk($relayId);
            if ($relay) {
                return $this->mapToResource($relay);
            }
        }

        if (isset($uriVariables['order_address_id'])) {
            $relay = MondialRelayPickupPointAddressQuery::create()
                ->filterByOrderAddressId($uriVariables['order_address_id'])
                ->findOne();
            if ($relay) {
                return $this->mapToResource($relay);
            }
        }

        if (isset($uriVariables['order_id'])) {
            $order = OrderQuery::create()->findPk($uriVariables['order_id']);
            if ($order) {
                $relay = MondialRelayPickupPointAddressQuery::create()
                    ->filterByOrderAddressId($order->getDeliveryOrderAddressId())
                    ->findOne();
                if ($relay) {
                    return $this->mapToResource($relay);
                }
            }
        }

        return null;
    }

    /**
     * @param $relay
     * @return MondialRelayPickupPointSelectedRelayResource|null
     */
    private function mapToResource($relay): ?MondialRelayPickupPointSelectedRelayResource
    {
        $relayDataRaw = $relay->getJsonRelayData();
        try {
            $relayData = json_decode($relayDataRaw, true, 512, JSON_THROW_ON_ERROR);
        } catch (\Throwable) {
            return null;
        }

        $resource = new MondialRelayPickupPointSelectedRelayResource();
        $resource->id        = $relayData['id'] ?? null;
        $resource->latitude  = $relayData['latitude'] ?? null;
        $resource->longitude = $relayData['longitude'] ?? null;
        $resource->zipcode   = $relayData['zipcode'] ?? null;
        $resource->city      = $relayData['city'] ?? null;
        $resource->country   = $relayData['country'] ?? null;
        $resource->name      = $relayData['name'] ?? null;
        $resource->address   = $relayData['address'] ?? null;
        $resource->distance  = $relayData['distance'] ?? null;
        $resource->openings  = $relayData['openings'] ?? null;

        return $resource;
    }
}
