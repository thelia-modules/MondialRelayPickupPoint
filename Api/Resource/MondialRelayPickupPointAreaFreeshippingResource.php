<?php

namespace MondialRelayPickupPoint\Api\Resource;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use Propel\Runtime\Map\TableMap;
use MondialRelayPickupPoint\Model\Map\MondialRelayPickupPointAreaFreeshippingTableMap;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\Ignore;
use Thelia\Api\Resource\PropelResourceInterface;
use Thelia\Api\Resource\PropelResourceTrait;

#[ApiResource(
    operations: [
        new Get(
            uriTemplate: '/front/mondial-relay-pickup-point-area-freeshipping/{id}',
            name: 'api_mondial_relay_pickup_point_area_freeshipping_get_front'
        ),
        new GetCollection(
            uriTemplate: '/front/mondial-relay-pickup-point-area-freeshipping',
            name: 'api_mondial_relay_pickup_point_area_freeshipping_get_collection_front'
        ),
    ],
    normalizationContext: ['groups' => [self::GROUP_FRONT_READ]]
)]
#[ApiResource(
    operations: [
        new Get(
            uriTemplate: '/admin/mondial-relay-pickup-point-area-freeshipping/{id}',
            name: 'api_mondial_relay_pickup_point_area_freeshipping_get_admin'
        ),
        new GetCollection(
            uriTemplate: '/admin/mondial-relay-pickup-point-area-freeshipping',
            name: 'api_mondial_relay_pickup_point_area_freeshipping_get_collection_admin'
        ),
    ],
    normalizationContext: ['groups' => [self::GROUP_ADMIN_READ]]
)]
class MondialRelayPickupPointAreaFreeshippingResource implements PropelResourceInterface
{
    use PropelResourceTrait;

    public const GROUP_ADMIN_READ = 'admin:mondial_relay_pickup_point_area_freeshipping:read';
    public const GROUP_FRONT_READ = 'front:mondial_relay_pickup_point_area_freeshipping:read';

    /**
     * @var int|null
     */
    #[Groups([self::GROUP_ADMIN_READ, self::GROUP_FRONT_READ])]
    public ?int $id = null;

    /**
     * @var int|null
     */
    #[Groups([self::GROUP_ADMIN_READ, self::GROUP_FRONT_READ])]
    public ?int $areaId = null;

    /**
     * @var float|null
     */
    #[Groups([self::GROUP_ADMIN_READ, self::GROUP_FRONT_READ])]
    public ?float $cartAmount = null;

    /**
     * @return TableMap|null
     */
    #[Ignore]
    public static function getPropelRelatedTableMap(): ?TableMap
    {
        return new MondialRelayPickupPointAreaFreeshippingTableMap();
    }

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param int|null $id
     * @return void
     */
    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    /**
     * @return int|null
     */
    public function getAreaId(): ?int
    {
        return $this->areaId;
    }

    /**
     * @param int|null $areaId
     * @return void
     */
    public function setAreaId(?int $areaId): void
    {
        $this->areaId = $areaId;
    }

    /**
     * @return float|null
     */
    public function getCartAmount(): ?float
    {
        return $this->cartAmount;
    }

    /**
     * @param float|null $cartAmount
     * @return void
     */
    public function setCartAmount(?float $cartAmount): void
    {
        $this->cartAmount = $cartAmount;
    }
}
