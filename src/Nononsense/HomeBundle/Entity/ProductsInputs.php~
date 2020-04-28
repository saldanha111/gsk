<?php

namespace Nononsense\HomeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

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
     * @var integer
     *
     * @ORM\Column(name="amount", type="integer",  nullable=true)
     */
    protected $amount;

    /**
     * @var integer
     *
     * @ORM\Column(name="remaining_amount", type="integer",  nullable=true)
     */
    protected $remainingAmount;
    
    /**
     * @ORM\Column(name="reception_date", type="datetime")
     */
    protected $receptionDate;

    /**
     * @ORM\Column(name="destruction_date", type="datetime")
     */
    protected $destructionDate;

    /**
     * @ORM\Column(name="expiry_date", type="datetime")
     */
    protected $expiryDate;

    /**
     * @ORM\Column(name="open_date", type="datetime")
     */
    protected $openDate;



    public function __construct()
    {
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
     * Set amount
     *
     * @param integer $amount
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
     * @return integer 
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * Set remainingAmount
     *
     * @param integer $remainingAmount
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
     * @return integer 
     */
    public function getRemainingAmount()
    {
        return $this->remainingAmount;
    }

    /**
     * Set receptionDate
     *
     * @param \reception_date $receptionDate
     * @return ProductsInputs
     */
    public function setReceptionDate(\reception_date $receptionDate)
    {
        $this->receptionDate = $receptionDate;

        return $this;
    }

    /**
     * Get receptionDate
     *
     * @return \reception_date 
     */
    public function getReceptionDate()
    {
        return $this->receptionDate;
    }

    /**
     * Set destructionDate
     *
     * @param \destruction_date $destructionDate
     * @return ProductsInputs
     */
    public function setDestructionDate(\destruction_date $destructionDate)
    {
        $this->destructionDate = $destructionDate;

        return $this;
    }

    /**
     * Get destructionDate
     *
     * @return \destruction_date 
     */
    public function getDestructionDate()
    {
        return $this->destructionDate;
    }

    /**
     * Set expiryDate
     *
     * @param \expiry_date $expiryDate
     * @return ProductsInputs
     */
    public function setExpiryDate(\expiry_date $expiryDate)
    {
        $this->expiryDate = $expiryDate;

        return $this;
    }

    /**
     * Get expiryDate
     *
     * @return \expiry_date 
     */
    public function getExpiryDate()
    {
        return $this->expiryDate;
    }

    /**
     * Set openDate
     *
     * @param \open_date $openDate
     * @return ProductsInputs
     */
    public function setOpenDate(\open_date $openDate)
    {
        $this->openDate = $openDate;

        return $this;
    }

    /**
     * Get openDate
     *
     * @return \open_date 
     */
    public function getOpenDate()
    {
        return $this->openDate;
    }

    /**
     * Set product
     *
     * @param \Nononsense\HomeBundle\Entity\Products $product
     * @return ProductsInputs
     */
    public function setProduct(\Nononsense\HomeBundle\Entity\Products $product = null)
    {
        $this->product = $product;

        return $this;
    }

    /**
     * Get product
     *
     * @return \Nononsense\HomeBundle\Entity\Products 
     */
    public function getProduct()
    {
        return $this->product;
    }
}
