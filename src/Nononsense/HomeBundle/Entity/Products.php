<?php

namespace Nononsense\HomeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity
 * @ORM\Table(name="products")
 * @ORM\Entity(repositoryClass="Nononsense\HomeBundle\Entity\ProductsRepository")
 * @ORM\HasLifecycleCallbacks
 */
class Products
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;


    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255,  nullable=true)
     */
    protected $name;

    /**
     * @var string
     *
     * @ORM\Column(name="cash_number", type="string", length=255,  nullable=true)
     */
    protected $cashNumber;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="string", length=255,  nullable=true)
     */
    protected $description;

    /**
     * @var string
     *
     * @ORM\Column(name="part_number", type="string", length=255, unique=true)
     */
    protected $partNumber;

    /**
     * @var integer
     *
     * @ORM\Column(name="stock", type="decimal", scale=2, nullable=true)
     */
    protected $stock;

    /**
     * @var string
     *
     * @ORM\Column(name="provider", type="string", length=255,  nullable=true)
     */
    protected $provider;

    /**
     * @var string
     *
     * @ORM\Column(name="presentation", type="string", length=255,  nullable=true)
     */
    protected $presentation;

    /**
     * @var integer
     *
     * @ORM\Column(name="stock_minimum", type="decimal", scale=2, nullable=true)
     */
    protected $stockMinimum;

    /**
     * @var string
     *
     * @ORM\Column(name="analysisMethod", type="string", length=255,  nullable=true)
     */
    protected $analysisMethod;

    /**
     * @var string
     *
     * @ORM\Column(name="observations", type="string", length=255,  nullable=true)
     */
    protected $observations;

    /**
     * @ORM\ManyToOne(targetEntity="\Nononsense\HomeBundle\Entity\ProductsTypes", inversedBy="products")
     * @ORM\JoinColumn(name="type_id", referencedColumnName="id")
     */
    protected $type;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $created;

    /**
     * @ORM\OneToMany(targetEntity="\Nononsense\HomeBundle\Entity\ProductsInputs", mappedBy="product")
     */
    protected $productsInputs;


    public function __construct()
    {
        $this->created = new \DateTime();
        $this->stock = 0;
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
     * Set name
     *
     * @param string $name
     * @return Products
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     * @return Products
     */
    public function setCreated($created)
    {
        $this->created = $created;

        return $this;
    }

    /**
     * Get created
     *
     * @return \DateTime 
     */
    public function getCreated()
    {
        return $this->created;
    }


    /**
     * Set description
     *
     * @param string $description
     * @return Products
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string 
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set partNumber
     *
     * @param string $partNumber
     * @return Products
     */
    public function setPartNumber($partNumber)
    {
        $this->partNumber = $partNumber;

        return $this;
    }

    /**
     * Get partNumber
     *
     * @return string 
     */
    public function getPartNumber()
    {
        return $this->partNumber;
    }

    /**
     * Set stock
     *
     * @param integer $stock
     * @return Products
     */
    public function setStock($stock)
    {
        $this->stock = $stock;

        return $this;
    }

    /**
     * Get stock
     *
     * @return integer 
     */
    public function getStock()
    {
        return $this->stock;
    }

    /**
     * Set provider
     *
     * @param string $provider
     * @return Products
     */
    public function setProvider($provider)
    {
        $this->provider = $provider;

        return $this;
    }

    /**
     * Get provider
     *
     * @return string 
     */
    public function getProvider()
    {
        return $this->provider;
    }

    /**
     * Set presentation
     *
     * @param string $presentation
     * @return Products
     */
    public function setPresentation($presentation)
    {
        $this->presentation = $presentation;

        return $this;
    }

    /**
     * Get presentation
     *
     * @return string 
     */
    public function getPresentation()
    {
        return $this->presentation;
    }

    /**
     * Set stockMinimum
     *
     * @param integer $stockMinimum
     * @return Products
     */
    public function setStockMinimum($stockMinimum)
    {
        $this->stockMinimum = $stockMinimum;

        return $this;
    }

    /**
     * Get stockMinimum
     *
     * @return integer 
     */
    public function getStockMinimum()
    {
        return $this->stockMinimum;
    }

    /**
     * Set analysisMethod
     *
     * @param string $analysisMethod
     * @return Products
     */
    public function setAnalysisMethod($analysisMethod)
    {
        $this->analysisMethod = $analysisMethod;

        return $this;
    }

    /**
     * Get analysisMethod
     *
     * @return string 
     */
    public function getAnalysisMethod()
    {
        return $this->analysisMethod;
    }

    /**
     * Set observations
     *
     * @param string $observations
     * @return Products
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
     * Set type
     *
     * @param \Nononsense\HomeBundle\Entity\ProductsTypes $type
     * @return Products
     */
    public function setType(\Nononsense\HomeBundle\Entity\ProductsTypes $type = null)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return \Nononsense\HomeBundle\Entity\ProductsTypes 
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Add productsInputs
     *
     * @param \Nononsense\HomeBundle\Entity\ProductsInputs $productsInputs
     * @return Products
     */
    public function addProductsInput(\Nononsense\HomeBundle\Entity\ProductsInputs $productsInputs)
    {
        $this->productsInputs[] = $productsInputs;

        return $this;
    }

    /**
     * Remove productsInputs
     *
     * @param \Nononsense\HomeBundle\Entity\ProductsInputs $productsInputs
     */
    public function removeProductsInput(\Nononsense\HomeBundle\Entity\ProductsInputs $productsInputs)
    {
        $this->productsInputs->removeElement($productsInputs);
    }

    /**
     * Get productsInputs
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getProductsInputs()
    {
        return $this->productsInputs;
    }

    /**
     * Set cashNumber
     *
     * @param string $cashNumber
     * @return Products
     */
    public function setCashNumber($cashNumber)
    {
        $this->cashNumber = $cashNumber;

        return $this;
    }

    /**
     * Get cashNumber
     *
     * @return string 
     */
    public function getCashNumber()
    {
        return $this->cashNumber;
    }
}
