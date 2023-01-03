<?php

namespace App\Entity;

use App\Repository\OrderRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: OrderRepository::class)]
#[ORM\Table(name: '`order`')]
class Order
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $date = null;

    #[ORM\Column]
    private ?int $idPaypal = null;

    #[ORM\ManyToOne(inversedBy: 'orders')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user_id = null;

    #[ORM\OneToMany(mappedBy: 'order_id', targetEntity: PurchasedProduct::class)]
    private Collection $purchasedProducts;

    public function __construct()
    {
        $this->purchasedProducts = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getIdPaypal(): ?int
    {
        return $this->idPaypal;
    }

    public function setIdPaypal(int $idPaypal): self
    {
        $this->idPaypal = $idPaypal;

        return $this;
    }

    public function getUserId(): ?User
    {
        return $this->user_id;
    }

    public function setUserId(?User $user_id): self
    {
        $this->user_id = $user_id;

        return $this;
    }

    /**
     * @return Collection<int, PurchasedProduct>
     */
    public function getPurchasedProducts(): Collection
    {
        return $this->purchasedProducts;
    }

    public function addPurchasedProduct(PurchasedProduct $purchasedProduct): self
    {
        if (!$this->purchasedProducts->contains($purchasedProduct)) {
            $this->purchasedProducts->add($purchasedProduct);
            $purchasedProduct->setOrderId($this);
        }

        return $this;
    }

    public function removePurchasedProduct(PurchasedProduct $purchasedProduct): self
    {
        if ($this->purchasedProducts->removeElement($purchasedProduct)) {
            // set the owning side to null (unless already changed)
            if ($purchasedProduct->getOrderId() === $this) {
                $purchasedProduct->setOrderId(null);
            }
        }

        return $this;
    }
}
