<?php
declare(strict_types=1);
namespace App\Entity;

use App\Repository\FactRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FactRepository::class)]
class Fact {
  #[ORM\Id]
  #[ORM\GeneratedValue]
  #[ORM\Column]
  private ?int $id = null;

  #[ORM\Column(type: Types::GUID)]
  private ?string $uuid = null;

  #[ORM\Column(length: 255)]
  private ?string $factTitle = null;

  #[ORM\Column(type: Types::TEXT)]
  private ?string $factText = null;

  #[ORM\Column(options: [
    "default" => 0,
  ])]
  private ?int $weight = null;

  #[ORM\Column(options: [
    "default" => true,
  ])]
  private ?bool $status = true;

  #[ORM\Column(nullable: true)]
  private ?\DateTimeImmutable $modifiedAt = null;

  #[ORM\Column]
  private ?\DateTimeImmutable $createdAt = null;

  public function __construct() {
    $now = (new \DateTimeImmutable)->setTimeZone(new \DateTimeZone('UTC'));
    $this->setCreatedAt($now);
    $this->setStatus(true);
    $this->setWeight(0);
  }

  public function getId(): ?int
  {
    return $this->id;
  }

  public function getUuid(): ?string
  {
    return $this->uuid;
  }

  public function setUuid(string $uuid): static
  {
    $this->uuid = $uuid;

    return $this;
  }

  public function getFactTitle(): ?string
  {
    return $this->factTitle;
  }

  public function setFactTitle(string $factTitle): static
  {
    $this->factTitle = $factTitle;

    return $this;
  }

  public function getFactText(): ?string
  {
    return $this->factText;
  }

  public function setFactText(string $factText): static
  {
    $this->factText = $factText;

    return $this;
  }

  public function getWeight(): ?int
  {
    return $this->weight;
  }

  public function setWeight(int $weight): static
  {
    $this->weight = $weight;

    return $this;
  }

  public function isStatus(): ?bool
  {
    return $this->status;
  }

  public function setStatus(bool $status): static
  {
    $this->status = $status;

    return $this;
  }

  public function getModifiedAt(): ?\DateTimeImmutable
  {
    return $this->modifiedAt;
  }

  public function setModifiedAt(?\DateTimeImmutable $modifiedAt): static
  {
    $this->modifiedAt = $modifiedAt;

    return $this;
  }

  public function getCreatedAt(): ?\DateTimeImmutable
  {
    return $this->createdAt;
  }

  public function setCreatedAt(\DateTimeImmutable $createdAt): static
  {
    $this->createdAt = $createdAt;

    return $this;
  }
}
