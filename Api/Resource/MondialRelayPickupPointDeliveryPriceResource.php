<?php

namespace MondialRelayPickupPoint\Api\Resource;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use MondialRelayPickupPoint\Api\State\MondialRelayPickupPointDeliveryPriceProvider;
use Symfony\Component\Serializer\Annotation\Groups;

#[ApiResource(
    operations: [
        new GetCollection(
            uriTemplate: '/front/mondial-relay-pickup-point-delivery-prices',
            name: 'api_mondial_relay_pickup_point_delivery_price_get_collection_front',
            provider: MondialRelayPickupPointDeliveryPriceProvider::class
        ),
    ],
    normalizationContext: ['groups' => [self::GROUP_FRONT_READ]]
)]
class MondialRelayPickupPointDeliveryPriceResource
{
    public const GROUP_FRONT_READ = 'front:mondial_relay_pickup_point_delivery_price:read';

    /**
     * @var float|null
     */
    #[Groups([self::GROUP_FRONT_READ])]
    public ?float $price = null;

    /**
     * @var float|null
     */
    #[Groups([self::GROUP_FRONT_READ])]
    public ?float $maxWeight = null;

    /**
     * @var int|null
     */
    #[Groups([self::GROUP_FRONT_READ])]
    public ?int $areaId = null;

    /**
     * @var int|null
     */
    #[Groups([self::GROUP_FRONT_READ])]
    public ?int $deliveryDelay = null;

    /**
     * @var \DateTimeInterface|string|null
     */
    #[Groups([self::GROUP_FRONT_READ])]
    public $deliveryDate = null;

    /**
     * @var bool|null
     */
    #[Groups([self::GROUP_FRONT_READ])]
    public ?bool $insuranceAvailable = null;

    /**
     * @var float|null
     */
    #[Groups([self::GROUP_FRONT_READ])]
    public ?float $insurancePrice = null;

    /**
     * @var float|null
     */
    #[Groups([self::GROUP_FRONT_READ])]
    public ?float $insuranceRefValue = null;


    /**
     * @return float|null
     */
    public function getPrice(): ?float
    {
        return $this->price;
    }

    /**
     * @param float|null $price
     */
    public function setPrice(?float $price): void
    {
        $this->price = $price;
    }

    /**
     * @return float|null
     */
    public function getMaxWeight(): ?float
    {
        return $this->maxWeight;
    }

    /**
     * @param float|null $maxWeight
     */
    public function setMaxWeight(?float $maxWeight): void
    {
        $this->maxWeight = $maxWeight;
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
     */
    public function setAreaId(?int $areaId): void
    {
        $this->areaId = $areaId;
    }

    /**
     * @return int|null
     */
    public function getDeliveryDelay(): ?int
    {
        return $this->deliveryDelay;
    }

    /**
     * @param int|null $deliveryDelay
     */
    public function setDeliveryDelay(?int $deliveryDelay): void
    {
        $this->deliveryDelay = $deliveryDelay;
    }

    /**
     * @return \DateTimeInterface|string|null
     */
    public function getDeliveryDate()
    {
        return $this->deliveryDate;
    }

    /**
     * @param \DateTimeInterface|string|null $deliveryDate
     */
    public function setDeliveryDate($deliveryDate): void
    {
        $this->deliveryDate = $deliveryDate;
    }

    /**
     * @return bool|null
     */
    public function getInsuranceAvailable(): ?bool
    {
        return $this->insuranceAvailable;
    }

    /**
     * @param bool|null $insuranceAvailable
     */
    public function setInsuranceAvailable(?bool $insuranceAvailable): void
    {
        $this->insuranceAvailable = $insuranceAvailable;
    }

    /**
     * @return float|null
     */
    public function getInsurancePrice(): ?float
    {
        return $this->insurancePrice;
    }

    /**
     * @param float|null $insurancePrice
     */
    public function setInsurancePrice(?float $insurancePrice): void
    {
        $this->insurancePrice = $insurancePrice;
    }

    /**
     * @return float|null
     */
    public function getInsuranceRefValue(): ?float
    {
        return $this->insuranceRefValue;
    }

    /**
     * @param float|null $insuranceRefValue
     */
    public function setInsuranceRefValue(?float $insuranceRefValue): void
    {
        $this->insuranceRefValue = $insuranceRefValue;
    }
}
