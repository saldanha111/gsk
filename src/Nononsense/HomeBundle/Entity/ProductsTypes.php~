<?php

namespace Nononsense\HomeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity
 * @ORM\Table(name="products_types")
 * @ORM\Entity(repositoryClass="Nononsense\HomeBundle\Entity\ProductsTypesRepository")
 * @ORM\HasLifecycleCallbacks
 */
class ProductsTypes
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
     * @var decimal
     *
     * @ORM\Column(name="destruction_months", type="decimal", scale=2)
     */
    protected $destructionMonths;

    /**
     * @var decimal
     *
     * @ORM\Column(name="expiration_months", type="decimal", scale=2)
     */
    protected $expirationMonths;

    /**
     * @ORM\OneToMany(targetEntity="\Nononsense\HomeBundle\Entity\Products", mappedBy="type")
     */
    protected $products;
   

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
     * Set name
     *
     * @param string $name
     * @return ProductsTypes
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
     * Set destructionMonths
     *
     * @param string $destructionMonths
     * @return ProductsTypes
     */
    public function setDestructionMonths($destructionMonths)
    {
        $this->destructionMonths = $destructionMonths;

        return $this;
    }

    /**
     * Get destructionMonths
     *
     * @return string 
     */
    public function getDestructionMonths()
    {
        return $this->destructionMonths;
    }

    /**
     * Set expirationMonths
     *
     * @param string $expirationMonths
     * @return ProductsTypes
     */
    public function setExpirationMonths($expirationMonths)
    {
        $this->expirationMonths = $expirationMonths;

        return $this;
    }

    /**
     * Get expirationMonths
     *
     * @return string 
     */
    public function getExpirationMonths()
    {
        return $this->expirationMonths;
    }

    /**
     * Add products
     *
     * @param \Nononsense\HomeBundle\Entity\Products $products
     * @return ProductsTypes
     */
    public function addProduct(\Nononsense\HomeBundle\Entity\Products $products)
    {
        $this->products[] = $products;

        return $this;
    }

    /**
     * Remove products
     *
     * @param \Nononsense\HomeBundle\Entity\Products $products
     */
    public function removeProduct(\Nononsense\HomeBundle\Entity\Products $products)
    {
        $this->products->removeElement($products);
    }

    /**
     * Get products
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getProducts()
    {
        return $this->products;
    }
}
