<?php declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use App\Repository\ParkRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Serializer\Annotation\Groups;

#[ApiResource(operations: [new Get(), new GetCollection()], normalizationContext: ['groups' => ['read_park']])]
#[ORM\Table(name: 'park')]
#[ORM\Entity(repositoryClass: ParkRepository::class)]
class Park implements \Stringable
{
    #[ORM\Id, ORM\Column(name: 'id', type: Types::INTEGER), ORM\GeneratedValue]
    #[Groups(['read_park'])]
    private int|null $id = null;

    #[ORM\Column(name: 'name', type: Types::STRING, length: 255)]
    #[Groups(['list_coaster', 'read_coaster', 'read_park'])]
    private string $name;

    #[ORM\Column(name: 'slug', type: Types::STRING, length: 255, unique: true)]
    #[Gedmo\Slug(fields: ['name'])]
    private string $slug;

    /** @var Collection<Coaster> */
    #[ORM\OneToMany(mappedBy: 'park', targetEntity: 'Coaster'), ORM\OrderBy(['status' => 'ASC', 'score' => 'DESC'])]
    private Collection $coasters;

    #[ORM\ManyToOne(targetEntity: 'Country'), ORM\JoinColumn(nullable: false)]
    #[Groups(['read_coaster', 'read_park'])]
    private Country|null $country = null;

    #[ORM\Column(name: 'latitude', type: Types::FLOAT, precision: 8, scale: 6, nullable: true)]
    #[Groups(['read_park'])]
    private float|null $latitude = null;

    #[ORM\Column(name: 'longitude', type: Types::FLOAT, precision: 8, scale: 6, nullable: true)]
    #[Groups(['read_park'])]
    private float|null $longitude = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Gedmo\Timestampable(on: 'create')]
    private \DateTimeInterface|null $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Gedmo\Timestampable(on: 'update')]
    private \DateTimeInterface|null $updatedAt = null;

    #[ORM\Column(type: Types::BOOLEAN)]
    private bool $enabled = false;

    public function __construct()
    {
        $this->coasters = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->name;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;
        return $this;
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): static
    {
        $this->slug = $slug;
        return $this;
    }

    public function addCoaster(Coaster $coaster): static
    {
        $this->coasters[] = $coaster;
        return $this;
    }

    public function removeCoaster(Coaster $coaster): void
    {
        $this->coasters->removeElement($coaster);
    }

    public function getCountry(): Country|null
    {
        return $this->country;
    }

    public function setCountry(Country $country): static
    {
        $this->country = $country;
        return $this;
    }

    public function getLatitude(): float|null
    {
        return $this->latitude;
    }

    public function setLatitude(float|null $latitude): static
    {
        $this->latitude = $latitude;
        return $this;
    }

    public function getLongitude(): float|null
    {
        return $this->longitude;
    }

    public function setLongitude(float|null $longitude): static
    {
        $this->longitude = $longitude;
        return $this;
    }

    public function getCreatedAt(): \DateTimeInterface|null
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface|null $createdAt): static
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getUpdatedAt(): \DateTimeInterface|null
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeInterface|null $updatedAt): static
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    /** @return Collection<Coaster> */
    public function getOpenedCoasters(): Collection
    {
        return $this->getCoasters()->filter(fn(Coaster $coaster) => $coaster->getStatus()->getId() == 1);
    }

    /** @return Collection<Coaster> */
    public function getCoasters(): Collection
    {
        return $this->coasters;
    }

    public function getId(): int|null
    {
        return $this->id;
    }

    /** @return Collection<Coaster> */
    public function getKiddies(): Collection
    {
        return $this->getCoasters()->filter(fn(Coaster $coaster) => $coaster->isKiddie() == 1);
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled): static
    {
        $this->enabled = $enabled;
        return $this;
    }
}
