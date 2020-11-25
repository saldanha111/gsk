<?php

namespace Nononsense\HomeBundle\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Nononsense\UserBundle\Entity\Users;

/**
 * @ORM\Entity
 * @ORM\Table(name="products_signatures")
 * @ORM\Entity(repositoryClass="Nononsense\HomeBundle\Entity\ProductsSignaturesRepository")
 * @ORM\HasLifecycleCallbacks
 */
class ProductsSignatures
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
     * @ORM\Column(name="action", type="string", length=255,  nullable=false)
     */
    protected $action;

    /**
     * @var string
     *
     * @ORM\Column(name="old_value", type="integer", nullable=false)
     */
    protected $oldValue;

    /**
     * @var string
     *
     * @ORM\Column(name="new_value", type="integer", nullable=false)
     */
    protected $newValue;

    /**
     * @var string
     *
     * @ORM\Column(name="signature", type="string", length=255, nullable=false)
     */
    protected $signature;

    /**
     * @var string
     *
     * @ORM\Column(name="observations", type="string", length=255, nullable=false)
     */
    protected $observations;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $date;

    /**
     * @ORM\ManyToOne(targetEntity="\Nononsense\UserBundle\Entity\Users", inversedBy="ProductsSignature")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false)
     */
    protected $user;

    /**
     * @ORM\ManyToOne(targetEntity="\Nononsense\HomeBundle\Entity\Products", inversedBy="ProductsStignature")
     * @ORM\JoinColumn(name="product_id", referencedColumnName="id", nullable=false)
     */
    protected $product;


    /**
     * Constructor
     */
    public function __construct()
    {
        $this->date = new DateTime();
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
     * Set action
     *
     * @param string $action
     * @return ProductsSignatures
     */
    public function setAction($action)
    {
        $this->action = $action;

        return $this;
    }

    /**
     * Get action
     *
     * @return string 
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * Set oldValue
     *
     * @param integer $oldValue
     * @return ProductsSignatures
     */
    public function setOldValue($oldValue)
    {
        $this->oldValue = $oldValue;

        return $this;
    }

    /**
     * Get oldValue
     *
     * @return integer 
     */
    public function getOldValue()
    {
        return $this->oldValue;
    }

    /**
     * Set newValue
     *
     * @param integer $newValue
     * @return ProductsSignatures
     */
    public function setNewValue($newValue)
    {
        $this->newValue = $newValue;

        return $this;
    }

    /**
     * Get newValue
     *
     * @return integer 
     */
    public function getNewValue()
    {
        return $this->newValue;
    }

    /**
     * Set signature
     *
     * @param string $signature
     * @return ProductsSignatures
     */
    public function setSignature($signature)
    {
        $this->signature = $signature;

        return $this;
    }

    /**
     * Get signature
     *
     * @return string 
     */
    public function getSignature()
    {
        return $this->signature;
    }

    /**
     * Set observations
     *
     * @param string $observations
     * @return ProductsSignatures
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
     * Set date
     *
     * @param DateTime $date
     * @return ProductsSignatures
     */
    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Get date
     *
     * @return DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Set user
     *
     * @param Users $user
     * @return ProductsSignatures
     */
    public function setUser(Users $user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return Users
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set product
     *
     * @param Products $product
     * @return ProductsSignatures
     */
    public function setProduct(Products $product)
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
}
