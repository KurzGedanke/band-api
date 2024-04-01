<?php

namespace App\Entity;

use App\Repository\BandRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: BandRepository::class)]
class Band
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $Name = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $genre = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $logo = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $image = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $instagram = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $spotify = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $appleMusic = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $bandcamp = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $description = null;

    #[ORM\ManyToMany(targetEntity: Festival::class, mappedBy: 'bands', cascade: ['persist'])]
    private Collection $festivals;

    #[ORM\OneToMany(targetEntity: TimeSlot::class, mappedBy: 'band')]
    private Collection $timeSlots;

    #[ORM\Column(length: 255)]
    private ?string $slug = null;

    public function __construct()
    {
        $this->festivals = new ArrayCollection();
        $this->timeSlots = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->Name;
    }

    public function setName(string $Name): static
    {
        $this->Name = $Name;

        return $this;
    }

    public function getGenre(): ?string
    {
        return $this->genre;
    }

    public function setGenre(?string $genre): static
    {
        $this->genre = $genre;

        return $this;
    }

    public function getLogo(): ?string
    {
        return $this->logo;
    }

    public function setLogo(?string $logo): static
    {
        $this->logo = $logo;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): static
    {
        $this->image = $image;

        return $this;
    }

    public function getInstagram(): ?string
    {
        return $this->instagram;
    }

    public function setInstagram(?string $instagram): static
    {
        $this->instagram = $instagram;

        return $this;
    }

    public function getSpotify(): ?string
    {
        return $this->spotify;
    }

    public function setSpotify(?string $spotify): static
    {
        $this->spotify = $spotify;

        return $this;
    }

    public function getAppleMusic(): ?string
    {
        return $this->appleMusic;
    }

    public function setAppleMusic(?string $appleMusic): static
    {
        $this->appleMusic = $appleMusic;

        return $this;
    }

    public function getBandcamp(): ?string
    {
        return $this->bandcamp;
    }

    public function setBandcamp(?string $bandcamp): static
    {
        $this->bandcamp = $bandcamp;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return Collection<int, Festival>
     */
//    public function getFestivals(): Collection
//    {
//        return $this->festivals;
//    }
//
//    public function addFestival(Festival $festival): static
//    {
//        if (!$this->festivals->contains($festival)) {
//            $this->festivals->add($festival);
//            $festival->addBand($this);
//        }
//
//        return $this;
//    }
//
//    public function removeFestival(Festival $festival): static
//    {
//        if ($this->festivals->removeElement($festival)) {
//            $festival->removeBand($this);
//        }
//
//        return $this;
//    }

    public function __toString(): string
    {
        return $this->Name;
    }

    /**
     * @return Collection<int, TimeSlot>
     */
    public function getTimeSlots(): Collection
    {
        return $this->timeSlots;
    }

    public function addTimeSlot(TimeSlot $timeSlot): static
    {
        if (!$this->timeSlots->contains($timeSlot)) {
            $this->timeSlots->add($timeSlot);
            $timeSlot->setBand($this);
        }

        return $this;
    }

    public function removeTimeSlot(TimeSlot $timeSlot): static
    {
        if ($this->timeSlots->removeElement($timeSlot)) {
            // set the owning side to null (unless already changed)
            if ($timeSlot->getBand() === $this) {
                $timeSlot->setBand(null);
            }
        }

        return $this;
    }

public function getSlug(): ?string
{
    return $this->slug;
}

public function setSlug(string $slug): static
{
    $this->slug = $slug;

    return $this;
}
}
