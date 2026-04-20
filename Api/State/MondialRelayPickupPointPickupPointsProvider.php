<?php

namespace MondialRelayPickupPoint\Api\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use MondialRelayPickupPoint\Api\Resource\MondialRelayPickupPointPickupPointsResource;
use MondialRelayPickupPoint\Event\FindRelayEvent;
use MondialRelayPickupPoint\Event\MondialRelayEvents;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class MondialRelayPickupPointPickupPointsProvider implements ProviderInterface
{
    /**
     * @param EventDispatcherInterface $dispatcher
     * @param RequestStack $requestStack
     */
    public function __construct(
        private EventDispatcherInterface $dispatcher,
        private RequestStack $requestStack
    ) {}

    /**
     * @param Operation $operation
     * @param array $uriVariables
     * @param array $context
     * @return object|array|null
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $request = $this->requestStack->getCurrentRequest();

        $countryId = $request?->query->get('country_id');
        $city = $request?->query->get('city');
        $zipcode = $request?->query->get('zipcode');
        $searchRadius = $request?->query->get('search_radius', 10);

        $event = new FindRelayEvent($countryId, $city, $zipcode, $searchRadius);
        $this->dispatcher->dispatch($event, MondialRelayEvents::FIND_RELAYS);

        $resources = [];
        foreach ($event->getPoints() as $point) {
            $resource = new MondialRelayPickupPointPickupPointsResource();
            $resource->id = isset($point['id']) ? (int)$point['id'] : null;
            $resource->latitude = isset($point['latitude']) ? (float)$point['latitude'] : null;
            $resource->longitude = isset($point['longitude']) ? (float)$point['longitude'] : null;
            $resource->zipcode = $point['zipcode'] ?? null;
            $resource->city = $point['city'] ?? null;
            $resource->country = $point['country'] ?? null;
            $resource->name = $point['name'] ?? null;
            $resource->address = $point['address'] ?? null;
            $resource->distance = isset($point['distance']) ? (float)$point['distance'] : null;
            $resource->openings = $point['openings'] ?? null;
            $resources[] = $resource;
        }

        return $resources;
    }
}
