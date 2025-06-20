<?php

namespace MondialRelayPickupPoint\Api\Resource;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use MondialRelayPickupPoint\Api\State\MondialRelayPickupPointPickupPointsProvider;
use Symfony\Component\Serializer\Annotation\Groups;

#[ApiResource(
    operations: [
        new GetCollection(
            uriTemplate: '/front/mondial-relay-pickup-point-pickup-points',
            name: 'api_mondial_relay_pickup_point_pickup_points_get_collection_front',
            provider: MondialRelayPickupPointPickupPointsProvider::class
        ),
    ],
    normalizationContext: ['groups' => [MondialRelayPickupPointPickupPointsResource::GROUP_FRONT_READ]]
)]
class MondialRelayPickupPointPickupPointsResource
{
    public const GROUP_FRONT_READ = 'front:mondial_relay_pickup_point_pickup_points:read';

    /**
     * @var int|null
     */
    #[Groups([self::GROUP_FRONT_READ])]
    public ?int $id = null;

    /**
     * @var float|null
     */
    #[Groups([self::GROUP_FRONT_READ])]
    public ?float $latitude = null;

    /**
     * @var float|null
     */
    #[Groups([self::GROUP_FRONT_READ])]
    public ?float $longitude = null;

    /**
     * @var string|null
     */
    #[Groups([self::GROUP_FRONT_READ])]
    public ?string $zipcode = null;

    /**
     * @var string|null
     */
    #[Groups([self::GROUP_FRONT_READ])]
    public ?string $city = null;

    /**
     * @var string|null
     */
    #[Groups([self::GROUP_FRONT_READ])]
    public ?string $country = null;

    /**
     * @var string|null
     */
    #[Groups([self::GROUP_FRONT_READ])]
    public ?string $name = null;

    /**
     * @var string|null
     */
    #[Groups([self::GROUP_FRONT_READ])]
    public ?string $address = null;

    /**
     * @var float|null
     */
    #[Groups([self::GROUP_FRONT_READ])]
    public ?float $distance = null;

    /**
     * @var array|null
     */
    #[Groups([self::GROUP_FRONT_READ])]
    public ?array $openings = null;

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
     * @return float|null
     */
    public function getLatitude(): ?float
    {
        return $this->latitude;
    }

    /**
     * @param float|null $latitude
     * @return void
     */
    public function setLatitude(?float $latitude): void
    {
        $this->latitude = $latitude;
    }

    /**
     * @return float|null
     */
    public function getLongitude(): ?float
    {
        return $this->longitude;
    }

    /**
     * @param float|null $longitude
     * @return void
     */
    public function setLongitude(?float $longitude): void
    {
        $this->longitude = $longitude;
    }

    /**
     * @return string|null
     */
    public function getZipcode(): ?string
    {
        return $this->zipcode;
    }

    /**
     * @param string|null $zipcode
     * @return void
     */
    public function setZipcode(?string $zipcode): void
    {
        $this->zipcode = $zipcode;
    }

    /**
     * @return string|null
     */
    public function getCity(): ?string
    {
        return $this->city;
    }

    /**
     * @param string|null $city
     * @return void
     */
    public function setCity(?string $city): void
    {
        $this->city = $city;
    }

    /**
     * @return string|null
     */
    public function getCountry(): ?string
    {
        return $this->country;
    }

    /**
     * @param string|null $country
     * @return void
     */
    public function setCountry(?string $country): void
    {
        $this->country = $country;
    }

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string|null $name
     * @return void
     */
    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return string|null
     */
    public function getAddress(): ?string
    {
        return $this->address;
    }

    /**
     * @param string|null $address
     * @return void
     */
    public function setAddress(?string $address): void
    {
        $this->address = $address;
    }

    /**
     * @return float|null
     */
    public function getDistance(): ?float
    {
        return $this->distance;
    }

    /**
     * @param float|null $distance
     * @return void
     */
    public function setDistance(?float $distance): void
    {
        $this->distance = $distance;
    }

    /**
     * @return array|null
     */
    public function getOpenings(): ?array
    {
        return $this->openings;
    }

    /**
     * @param array|null $openings
     * @return void
     */
    public function setOpenings(?array $openings): void
    {
        $this->openings = $openings;
    }
}
