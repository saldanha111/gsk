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
     * @var decimal
     *
     * @ORM\Column(name="amount", type="decimal", scale=2,  nullable=true)
     */
    protected $amount;

    /**
     * @var decimal
     *
     * @ORM\Column(name="remaining_amount", type="decimal", scale=2,  nullable=true)
     */
    protected $remainingAmount;
    
    /**
     * @ORM\Column(name="reception_date", type="datetime", nullable=true)
     */
    protected $receptionDate;

    /**
     * @ORM\Column(name="destruction_date", type="datetime")
     */
    protected $destructionDate;

    /**
     * @ORM\Column(name="expiry_date", type="datetime", nullable=true)
     */
    protected $expiryDate;

    /**
     * @ORM\Column(name="open_date", type="datetime", nullable=true)
     */
    protected $openDate;

    /**
     * @ORM\OneToMany(targetEntity="\Nononsense\HomeBundle\Entity\ProductsOutputs", mappedBy="productInput")
     */
    protected $productsOutputs;



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
     * @param \DateTime $receptionDate
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
     * @return \DateTime 
     */
    public function getReceptionDate()
    {
        return $this->receptionDate;
    }

    /**
     * Set destructionDate
     *
     * @param \DateTime $destructionDate
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
     * @return \DateTime 
     */
    public function getDestructionDate()
    {
        return $this->destructionDate;
    }

    /**
     * Set expiryDate
     *
     * @param \DateTime $expiryDate
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
     * @return \DateTime 
     */
    public function getExpiryDate()
    {
        return $this->expiryDate;
    }

    /**
     * Set openDate
     *
     * @param \DateTime $openDate
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
     * @return \DateTime 
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

    /**
     * Add productsOutputs
     *
     * @param \Nononsense\HomeBundle\Entity\ProductsOutputs $productsOutputs
     * @return ProductsInputs
     */
    public function addProductsOutput(\Nononsense\HomeBundle\Entity\ProductsOutputs $productsOutputs)
    {
        $this->productsOutputs[] = $productsOutputs;

        return $this;
    }

    /**
     * Remove productsOutputs
     *
     * @param \Nononsense\HomeBundle\Entity\ProductsOutputs $productsOutputs
     */
    public function removeProductsOutput(\Nononsense\HomeBundle\Entity\ProductsOutputs $productsOutputs)
    {
        $this->productsOutputs->removeElement($productsOutputs);
    }

    /**
     * Get productsOutputs
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getProductsOutputs()
    {
        return $this->productsOutputs;
    }
}
