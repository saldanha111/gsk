<?php

namespace Nononsense\HomeBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="products_input_status")
 * @ORM\Entity(repositoryClass="Nononsense\HomeBundle\Entity\ProductsInputStatusRepository")
 * @ORM\HasLifecycleCallbacks
 */
class ProductsInputStatus
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     * @ORM\Column(name="slug", type="string", length=255,  nullable=false)
     */
    protected $slug;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255,  nullable=false)
     */
    protected $name;

    /**
     * @ORM\OneToMany(targetEntity="\Nononsense\HomeBundle\Entity\Products", mappedBy="state")
     */
    protected $productsInputs;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->productsInputs = new ArrayCollection();
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
     * Set slug
     *
     * @param string $slug
     * @return ProductsInputStatus
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;

        return $this;
    }

    /**
     * Get slug
     *
     * @return string 
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return ProductsInputStatus
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
     * Add productsInputs
     *
     * @param Products $productsInputs
     * @return ProductsInputStatus
     */
    public function addProductsInput(Products $productsInputs)
    {
        $this->productsInputs[] = $productsInputs;

        return $this;
    }

    /**
     * Remove productsInputs
     *
     * @param Products $productsInputs
     */
    public function removeProductsInput(Products $productsInputs)
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
}
