<?php

namespace Nononsense\HomeBundle\Entity;

use DateTime;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity
 * @ORM\Table(name="products_dissolution")
 * @ORM\Entity(repositoryClass="Nononsense\HomeBundle\Entity\ProductsDissolutionRepository")
 * @ORM\HasLifecycleCallbacks
 */
class ProductsDissolution
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
     * @ORM\ManyToMany(targetEntity="\Nononsense\HomeBundle\Entity\ProductsInputs", inversedBy="dissolutions")
     * @ORM\JoinTable(name="product_dissolution_inputs")
     */
    private $lines;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $created;

    /**
     * @var bool
     *
     * @ORM\Column(name="active", type="boolean")
     */
    private $active;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->lines = new ArrayCollection();
        $this->active = true;
        $this->created = new DateTime();
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
     * @return ProductsDissolution
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
     * @return ProductsDissolution
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
     * Set created
     *
     * @param \DateTime $created
     * @return ProductsDissolution
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
     * Set active
     *
     * @param boolean $active
     * @return ProductsDissolution
     */
    public function setActive($active)
    {
        $this->active = $active;

        return $this;
    }

    /**
     * Get active
     *
     * @return boolean 
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * Add lines
     *
     * @param ProductsInputs $lines
     * @return ProductsDissolution
     */
    public function addLine(ProductsInputs $lines)
    {
        $this->lines[] = $lines;

        return $this;
    }

    /**
     * Remove lines
     *
     * @param ProductsInputs $lines
     */
    public function removeLine(ProductsInputs $lines)
    {
        $this->lines->removeElement($lines);
    }

    /**
     * Get lines
     *
     * @return Collection
     */
    public function getLines()
    {
        return $this->lines;
    }
}
