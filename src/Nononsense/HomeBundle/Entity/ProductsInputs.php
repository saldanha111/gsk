<?php

namespace Nononsense\HomeBundle\Entity;

use DateTime;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity
 * @ORM\Table(name="products_inputs")
 * @ORM\Entity(repositoryClass="Nononsense\HomeBundle\Entity\ProductsInputsRepository")
 * @ORM\HasLifecycleCallbacks
 */
class ProductsInputs
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="\Nononsense\HomeBundle\Entity\Products", inversedBy="productsInputs")
     * @ORM\JoinColumn(name="product_id", referencedColumnName="id")
     */
    protected $product;

    /**
     * @var string
     *
     * @ORM\Column(name="qr_code", type="string", length=255, nullable=true)
     */
    protected $qrCode;

    /**
     * @var string
     *
     * @ORM\Column(name="lot_number", type="string", nullable=true)
     */
    protected $lotNumber;

    /**
     * @var int
     *
     * @ORM\Column(name="amount", type="integer",  nullable=false, options={"default" : 1})
     */
    protected $amount;

    /**
     * @var int
     *
     * @ORM\Column(name="remaining_amount", type="integer", nullable=false, options={"default" : 1})
     */
    protected $remainingAmount;
    
    /**
     * @var DateTime
     *
     * @ORM\Column(name="reception_date", type="datetime", nullable=false)
     */
    protected $receptionDate;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="destruction_date", type="datetime", nullable=true)
     */
    protected $destructionDate;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="expiry_date", type="datetime", nullable=true)
     */
    protected $expiryDate;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="open_date", type="datetime", nullable=true)
     */
    protected $openDate;

    /**
     * @ORM\OneToMany(targetEntity="\Nononsense\HomeBundle\Entity\ProductsOutputs", mappedBy="productInput")
     */
    protected $productsOutputs;

    /**
     * @ORM\ManyToOne(targetEntity="\Nononsense\HomeBundle\Entity\ProductsInputStatus", inversedBy="productsInputStatus")
     * @ORM\JoinColumn(name="state", referencedColumnName="id", nullable=false)
     */
    protected $state;

    /**
     * @var string
     *
     * @ORM\Column(name="observations", type="string", nullable=true)
     */
    protected $observations;

    /**
     * @ORM\ManyToOne(targetEntity="\Nononsense\UserBundle\Entity\Users", inversedBy="ProductsInput")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false)
     */
    protected $user;

    /**
     * @ORM\ManyToMany(targetEntity="\Nononsense\HomeBundle\Entity\ProductsDissolution", mappedBy="lines")
     */
    protected $dissolutions;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->productsOutputs = new ArrayCollection();
    }

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set lotNumber
     *
     * @param string $lotNumber
     * @return ProductsInputs
     */
    public function setLotNumber($lotNumber)
    {
        $this->lotNumber = $lotNumber;

        return $this;
    }

    /**
     * Get lotNumber
     *
     * @return string 
     */
    public function getLotNumber()
    {
        return $this->lotNumber;
    }

    /**
     * Set amount
     *
     * @param int $amount
     * @return ProductsInputs
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;

        return $this;
    }

    /**
     * Get amount
     *
     * @return int
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * Set remainingAmount
     *
     * @param int $remainingAmount
     * @return ProductsInputs
     */
    public function setRemainingAmount($remainingAmount)
    {
        $this->remainingAmount = $remainingAmount;

        return $this;
    }

    /**
     * Get remainingAmount
     *
     * @return int
     */
    public function getRemainingAmount()
    {
        return $this->remainingAmount;
    }

    /**
     * Set receptionDate
     *
     * @param DateTime $receptionDate
     * @return ProductsInputs
     */
    public function setReceptionDate($receptionDate)
    {
        $this->receptionDate = $receptionDate;

        return $this;
    }

    /**
     * Get receptionDate
     *
     * @return DateTime
     */
    public function getReceptionDate()
    {
        return $this->receptionDate;
    }

    /**
     * Set destructionDate
     *
     * @param DateTime $destructionDate
     * @return ProductsInputs
     */
    public function setDestructionDate($destructionDate)
    {
        $this->destructionDate = $destructionDate;

        return $this;
    }

    /**
     * Get destructionDate
     *
     * @return DateTime
     */
    public function getDestructionDate()
    {
        return $this->destructionDate;
    }

    /**
     * Set expiryDate
     *
     * @param DateTime $expiryDate
     * @return ProductsInputs
     */
    public function setExpiryDate($expiryDate)
    {
        $this->expiryDate = $expiryDate;

        return $this;
    }

    /**
     * Get expiryDate
     *
     * @return DateTime
     */
    public function getExpiryDate()
    {
        return $this->expiryDate;
    }

    /**
     * Set openDate
     *
     * @param DateTime $openDate
     * @return ProductsInputs
     */
    public function setOpenDate($openDate)
    {
        $this->openDate = $openDate;

        return $this;
    }

    /**
     * Get openDate
     *
     * @return DateTime
     */
    public function getOpenDate()
    {
        return $this->openDate;
    }

    /**
     * Set observations
     *
     * @param string $observations
     * @return ProductsInputs
     */
    public function setObservations($observations)
    {
        $this->observations = $observations;

        return $this;
    }

    /**
     * Get observations
     *
     * @return string 
     */
    public function getObservations()
    {
        return $this->observations;
    }

    /**
     * Set product
     *
     * @param Products $product
     * @return ProductsInputs
     */
    public function setProduct(Products $product = null)
    {
        $this->product = $product;

        return $this;
    }

    /**
     * Get product
     *
     * @return Products
     */
    public function getProduct()
    {
        return $this->product;
    }

    /**
     * Add productsOutputs
     *
     * @param ProductsOutputs $productsOutputs
     * @return ProductsInputs
     */
    public function addProductsOutput(ProductsOutputs $productsOutputs)
    {
        $this->productsOutputs[] = $productsOutputs;

        return $this;
    }

    /**
     * Remove productsOutputs
     *
     * @param ProductsOutputs $productsOutputs
     */
    public function removeProductsOutput(ProductsOutputs $productsOutputs)
    {
        $this->productsOutputs->removeElement($productsOutputs);
    }

    /**
     * Get productsOutputs
     *
     * @return Collection
     */
    public function getProductsOutputs()
    {
        return $this->productsOutputs;
    }

    /**
     * Set state
     *
     * @param ProductsInputStatus $state
     * @return ProductsInputs
     */
    public function setState(ProductsInputStatus $state)
    {
        $this->state = $state;

        return $this;
    }

    /**
     * Get state
     *
     * @return ProductsInputStatus
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * Set qrCode
     *
     * @param string $qrCode
     * @return ProductsInputs
     */
    public function setQrCode($qrCode)
    {
        $this->qrCode = $qrCode;

        return $this;
    }

    /**
     * Get qrCode
     *
     * @return string 
     */
    public function getQrCode()
    {
        return $this->qrCode;
    }

    /**
     * Set user
     *
     * @param \Nononsense\UserBundle\Entity\Users $user
     * @return ProductsInputs
     */
    public function setUser(\Nononsense\UserBundle\Entity\Users $user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \Nononsense\UserBundle\Entity\Users 
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Add dissolutions
     *
     * @param \Nononsense\HomeBundle\Entity\ProductsDissolution $dissolutions
     * @return ProductsInputs
     */
    public function addDissolution(\Nononsense\HomeBundle\Entity\ProductsDissolution $dissolutions)
    {
        $this->dissolutions[] = $dissolutions;

        return $this;
    }

    /**
     * Remove dissolutions
     *
     * @param \Nononsense\HomeBundle\Entity\ProductsDissolution $dissolutions
     */
    public function removeDissolution(\Nononsense\HomeBundle\Entity\ProductsDissolution $dissolutions)
    {
        $this->dissolutions->removeElement($dissolutions);
    }

    /**
     * Get dissolutions
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getDissolutions()
    {
        return $this->dissolutions;
    }
}
