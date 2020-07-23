<?php

namespace Nononsense\HomeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity
 * @ORM\Table(name="products_outputs")
 * @ORM\Entity(repositoryClass="Nononsense\HomeBundle\Entity\ProductsOutputsRepository")
 * @ORM\HasLifecycleCallbacks
 */
class ProductsOutputs
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="\Nononsense\HomeBundle\Entity\ProductsInputs", inversedBy="productsOutputs")
     * @ORM\JoinColumn(name="product_input_id", referencedColumnName="id")
     */
    protected $productInput;

    /**
     * @var decimal
     *
     * @ORM\Column(name="amount", type="decimal", scale=2)
     */
    protected $amount;

    /**
     * @ORM\Column(name="date", type="datetime")
     */
    protected $date;

    /**
     * @ORM\ManyToOne(targetEntity="\Nononsense\UserBundle\Entity\Users", inversedBy="productsOutput")
     * @ORM\JoinColumn(name="user", referencedColumnName="id", nullable=false)
     */
    protected $user;

    public function __construct()
    {
        $this->date = new \DateTime();
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
     * @param string $amount
     * @return ProductsOutputs
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;

        return $this;
    }

    /**
     * Get amount
     *
     * @return string 
     */
    public function getAmount()
    {
        return $this->amount;
    }


    /**
     * Set productInput
     *
     * @param \Nononsense\HomeBundle\Entity\ProductsInputs $productInput
     * @return ProductsOutputs
     */
    public function setProductInput(\Nononsense\HomeBundle\Entity\ProductsInputs $productInput = null)
    {
        $this->productInput = $productInput;

        return $this;
    }

    /**
     * Get productInput
     *
     * @return \Nononsense\HomeBundle\Entity\ProductsInputs 
     */
    public function getProductInput()
    {
        return $this->productInput;
    }

    /**
     * Set date
     *
     * @param \DateTime $date
     * @return ProductsOutputs
     */
    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Get date
     *
     * @return \DateTime 
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Set user
     *
     * @param \Nononsense\UserBundle\Entity\Users $user
     * @return ProductsOutputs
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
}
