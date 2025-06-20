<?php

namespace MondialRelayPickupPoint\Api\Resource;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use MondialRelayPickupPoint\Api\State\MondialRelayPickupPointSelectedRelayProvider;
use Symfony\Component\Serializer\Annotation\Groups;

#[ApiResource(
    operations: [
        new Get(
            uriTemplate: '/front/mondial-relay-pickup-point-selected-relay/{id}',
            name: 'api_mondial_relay_pickup_point_selected_relay_get_front',
            provider: MondialRelayPickupPointSelectedRelayProvider::class
        ),
        new GetCollection(
            uriTemplate: '/front/mondial-relay-pickup-point-selected-relays',
            name: 'api_mondial_relay_pickup_point_selected_relay_get_collection_front',
            provider: MondialRelayPickupPointSelectedRelayProvider::class
        ),
    ],
    normalizationContext: ['groups' => [MondialRelayPickupPointSelectedRelayResource::GROUP_FRONT_READ]]
)]
class MondialRelayPickupPointSelectedRelayResource
{
    public const GROUP_FRONT_READ = 'front:mondial_relay_pickup_point_selected_relay:read';

    /** @var int|null */
    #[Groups([self::GROUP_FRONT_READ])]
    public ?int $id = null;

    /** @var float|null */
    #[Groups([self::GROUP_FRONT_READ])]
    public ?float $latitude = null;

    /** @var float|null */
    #[Groups([self::GROUP_FRONT_READ])]
    public ?float $longitude = null;

    /** @var string|null */
    #[Groups([self::GROUP_FRONT_READ])]
    public ?string $zipcode = null;

    /** @var string|null */
    #[Groups([self::GROUP_FRONT_READ])]
    public ?string $city = null;

    /** @var string|null */
    #[Groups([self::GROUP_FRONT_READ])]
    public ?string $country = null;

    /** @var string|null */
    #[Groups([self::GROUP_FRONT_READ])]
    public ?string $name = null;

    /** @var string|null */
    #[Groups([self::GROUP_FRONT_READ])]
    public ?string $address = null;

    /** @var float|string|null */
    #[Groups([self::GROUP_FRONT_READ])]
    public $distance = null;

    /** @var array|string|null */
    #[Groups([self::GROUP_FRONT_READ])]
    public $openings = null;

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
     * @return float|string|null
     */
    public function getDistance(): float|string|null
    {
        return $this->distance;
    }

    /**
     * @param float|string|null $distance
     * @return void
     */
    public function setDistance(float|string|null $distance): void
    {
        $this->distance = $distance;
    }

    /**
     * @return array|string|null
     */
    public function getOpenings(): array|string|null
    {
        return $this->openings;
    }

    /**
     * @param array|string|null $openings
     * @return void
     */
    public function setOpenings(array|string|null $openings): void
    {
        $this->openings = $openings;
    }
}
