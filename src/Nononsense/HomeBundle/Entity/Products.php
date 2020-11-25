<?php

namespace Nononsense\HomeBundle\Entity;

use DateTime;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use DoctrineExtensions\Query\Mysql\Date;

/**
 * @ORM\Entity
 * @ORM\Table(name="products",
 *     indexes={
 *          @ORM\Index(columns={"type_id"})
 *      })
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
     * @ORM\Column(name="name", type="string", length=255,  nullable=false)
     */
    protected $name;

    /**
     * @var string
     *
     * @ORM\Column(name="qr_code", type="string", length=255, nullable=true)
     */
    protected $qrCode;

    /**
     * @var string
     *
     * @ORM\Column(name="internal_code", type="string", length=255, nullable=false)
     */
    protected $internalCode;

    /**
     * @var string
     *
     * @ORM\Column(name="cas_number", type="string", length=255, nullable=true)
     */
    protected $casNumber;

    /**
     * @var string
     *
     * @ORM\Column(name="part_number", type="string", length=255, nullable=false, unique=true)
     */
    protected $partNumber;

    /**
     * @var int
     *
     * @ORM\Column(name="stock", type="integer", nullable=false, options={"default" : 0} )
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
     * @var int
     *
     * @ORM\Column(name="stock_minimum", type="integer", nullable=true)
     */
    protected $stockMinimum;

    /**
     * @ORM\ManyToOne(targetEntity="\Nononsense\HomeBundle\Entity\ProductsTypes", inversedBy="products")
     * @ORM\JoinColumn(name="type_id", referencedColumnName="id", nullable=false)
     */
    protected $type;

    /**
     * @ORM\OneToMany(targetEntity="\Nononsense\HomeBundle\Entity\ProductsInputs", mappedBy="product")
     */
    protected $productsInputs;

    /**
     * @ORM\OneToMany(targetEntity="\Nononsense\HomeBundle\Entity\ProductsSignatures", mappedBy="product")
     */
    protected $productsSignatures;
    
    /**
     * @var bool
     *
     * @ORM\Column(name="active", type="boolean")
     */
    private $active;

    /**
     * @var bool
     *
     * @ORM\Column(name="destroyed", type="boolean", options={"default" : 0})
     */
    private $destroyed;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $created;

    /**
     * @var bool
     *
     * @ORM\Column(name="static", type="boolean",  nullable=true, options={"default" : false})
     */
    private $static;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->productsInputs = new ArrayCollection();
        $this->stock = 0;
        $this->destroyed = 0;
        $this->created = new DateTime();
        $this->active = 1;
        $this->static = false;
    }

    /**
     * Get id
     *
     * @return int
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
     * Set qrCode
     *
     * @param string $qrCode
     * @return Products
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
     * Set casNumber
     *
     * @param string $casNumber
     * @return Products
     */
    public function setCasNumber($casNumber)
    {
        $this->casNumber = $casNumber;

        return $this;
    }

    /**
     * Get casNumber
     *
     * @return string 
     */
    public function getCasNumber()
    {
        return $this->casNumber;
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
     * @param int $stock
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
     * @return int
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
     * Set stockMinimum
     *
     * @param string $stockMinimum
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
     * @return string 
     */
    public function getStockMinimum()
    {
        return $this->stockMinimum;
    }

    /**
     * Set active
     *
     * @param bool $active
     * @return Products
     */
    public function setActive($active)
    {
        $this->active = $active;

        return $this;
    }

    /**
     * Get active
     *
     * @return bool
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * Set destroyed
     *
     * @param bool $destroyed
     * @return Products
     */
    public function setDestroyed($destroyed)
    {
        $this->destroyed = $destroyed;

        return $this;
    }

    /**
     * Get destroyed
     *
     * @return bool
     */
    public function getDestroyed()
    {
        return $this->destroyed;
    }

    /**
     * Set created
     *
     * @param DateTime $created
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
     * @return DateTime
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Set type
     *
     * @param ProductsTypes $type
     * @return Products
     */
    public function setType(ProductsTypes $type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return ProductsTypes
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Add productsInputs
     *
     * @param ProductsInputs $productsInputs
     * @return Products
     */
    public function addProductsInput(ProductsInputs $productsInputs)
    {
        $this->productsInputs[] = $productsInputs;

        return $this;
    }

    /**
     * Remove productsInputs
     *
     * @param ProductsInputs $productsInputs
     */
    public function removeProductsInput(ProductsInputs $productsInputs)
    {
        $this->productsInputs->removeElement($productsInputs);
    }

    /**
     * Get productsInputs
     *
     * @return Collection
     */
    public function getProductsInputs()
    {
        return $this->productsInputs;
    }

    /**
     * Set internalCode
     *
     * @param string $internalCode
     * @return Products
     */
    public function setInternalCode($internalCode)
    {
        $this->internalCode = $internalCode;

        return $this;
    }

    /**
     * Get internalCode
     *
     * @return string 
     */
    public function getInternalCode()
    {
        return $this->internalCode;
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
     * Set static
     *
     * @param boolean $static
     * @return Products
     */
    public function setStatic($static)
    {
        $this->static = $static;

        return $this;
    }

    /**
     * Get static
     *
     * @return boolean 
     */
    public function getStatic()
    {
        return $this->static;
    }

    /**
     * Add productsSignatures
     *
     * @param \Nononsense\HomeBundle\Entity\ProductsSignatures $productsSignatures
     * @return Products
     */
    public function addProductsSignature(\Nononsense\HomeBundle\Entity\ProductsSignatures $productsSignatures)
    {
        $this->productsSignatures[] = $productsSignatures;

        return $this;
    }

    /**
     * Remove productsSignatures
     *
     * @param \Nononsense\HomeBundle\Entity\ProductsSignatures $productsSignatures
     */
    public function removeProductsSignature(\Nononsense\HomeBundle\Entity\ProductsSignatures $productsSignatures)
    {
        $this->productsSignatures->removeElement($productsSignatures);
    }

    /**
     * Get productsSignatures
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getProductsSignatures()
    {
        return $this->productsSignatures;
    }
}
