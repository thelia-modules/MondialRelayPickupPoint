<?php

namespace MondialRelayPickupPoint\Api\Resource;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use Propel\Runtime\Map\TableMap;
use MondialRelayPickupPoint\Model\Map\MondialRelayPickupPointFreeshippingTableMap;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\Ignore;
use Thelia\Api\Resource\PropelResourceInterface;
use Thelia\Api\Resource\PropelResourceTrait;

#[ApiResource(
    operations: [
        new Get(
            uriTemplate: '/front/mondial-relay-pickup-point-freeshipping/{id}',
            name: 'api_mondial_relay_pickup_point_freeshipping_get_front'
        ),
        new GetCollection(
            uriTemplate: '/front/mondial-relay-pickup-point-freeshippings',
            name: 'api_mondial_relay_pickup_point_freeshipping_get_collection_front'
        ),
    ],
    normalizationContext: ['groups' => [self::GROUP_FRONT_READ]]
)]
#[ApiResource(
    operations: [
        new Get(
            uriTemplate: '/admin/mondial-relay-pickup-point-freeshipping/{id}',
            name: 'api_mondial_relay_pickup_point_freeshipping_get_admin'
        ),
        new GetCollection(
            uriTemplate: '/admin/mondial-relay-pickup-point-freeshippings',
            name: 'api_mondial_relay_pickup_point_freeshipping_get_collection_admin'
        ),
    ],
    normalizationContext: ['groups' => [self::GROUP_ADMIN_READ]]
)]
class MondialRelayPickupPointFreeshippingResource implements PropelResourceInterface
{
    use PropelResourceTrait;

    public const GROUP_ADMIN_READ = 'admin:mondial_relay_pickup_point_freeshipping:read';
    public const GROUP_FRONT_READ = 'front:mondial_relay_pickup_point_freeshipping:read';

    /**
     * @var int|null
     */
    #[Groups([self::GROUP_ADMIN_READ, self::GROUP_FRONT_READ])]
    public ?int $id = null;

    /**
     * @var bool|null
     */
    #[Groups([self::GROUP_ADMIN_READ, self::GROUP_FRONT_READ])]
    public ?bool $freeshippingActive = null;

    /**
     * @var float|null
     */
    #[Groups([self::GROUP_ADMIN_READ, self::GROUP_FRONT_READ])]
    public ?float $freeshippingFrom = null;

    /**
     * @return TableMap|null
     */
    #[Ignore]
    public static function getPropelRelatedTableMap(): ?TableMap
    {
        return new MondialRelayPickupPointFreeshippingTableMap();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    /**
     * @return bool|null
     */
    public function getFreeshippingActive(): ?bool
    {
        return $this->freeshippingActive;
    }

    /**
     * @param bool|null $freeshippingActive
     * @return void
     */
    public function setFreeshippingActive(?bool $freeshippingActive): void
    {
        $this->freeshippingActive = $freeshippingActive;
    }

    /**
     * @return float|null
     */
    public function getFreeshippingFrom(): ?float
    {
        return $this->freeshippingFrom;
    }

    /**
     * @param float|null $freeshippingFrom
     * @return void
     */
    public function setFreeshippingFrom(?float $freeshippingFrom): void
    {
        $this->freeshippingFrom = $freeshippingFrom;
    }
}
