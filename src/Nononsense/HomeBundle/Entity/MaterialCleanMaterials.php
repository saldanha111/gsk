<?php

namespace Nononsense\HomeBundle\Entity;

use DateTime;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="mc_materials")
 * @ORM\Entity(repositoryClass="Nononsense\HomeBundle\Entity\MaterialCleanMaterialsRepository")
 * @ORM\HasLifecycleCallbacks
 */
class MaterialCleanMaterials
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
     * @ORM\Column(name="name", type="string", length=255,  nullable=false, unique=true)
     */
    protected $name;

    /**
     * @var int
     *
     * @ORM\Column(name="expiration_days", type="integer")
     */
    protected $expirationDays;

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
     * @ORM\OneToMany(targetEntity="\Nononsense\HomeBundle\Entity\MaterialCleanCodes", mappedBy="idMaterial")
     */
    private $barcode;

    /**
     * @ORM\OneToMany(targetEntity="\Nononsense\HomeBundle\Entity\MaterialCleanCleans", mappedBy="material")
     */
    private $cleans;

    /**
     * @var bool
     *
     * @ORM\Column(name="additional_info", type="boolean")
     */
    private $additionalInfo;

    /**
     * @var bool
     *
     * @ORM\Column(name="other_name", type="boolean", nullable=false, options={"default" : 0})
     */
    private $otherName;

    /**
     * @ORM\ManyToOne(targetEntity="\Nononsense\HomeBundle\Entity\MaterialCleanProducts", inversedBy="material")
     * @ORM\JoinColumn(name="id_product", referencedColumnName="id")
     */
    private $product;

    public function __construct()
    {
        $this->created = new DateTime();
        $this->expirationDays = 30;
        $this->active = 1;
        $this->additionalInfo = 0;
        $this->otherName = 0;
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
     * @return MaterialCleanMaterials
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
     * Set expirationDays
     *
     * @param int $expirationDays
     * @return MaterialCleanMaterials
     */
    public function setExpirationDays($expirationDays)
    {
        $this->expirationDays = $expirationDays;

        return $this;
    }

    /**
     * Get expirationDays
     *
     * @return int
     */
    public function getExpirationDays()
    {
        return $this->expirationDays;
    }

    /**
     * Set created
     *
     * @param DateTime $created
     * @return MaterialCleanMaterials
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
     * Set active
     *
     * @param bool $active
     * @return MaterialCleanMaterials
     */
    public function setActive($active)
    {
        $this->active = ($active)?: false;

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
     * Add barcode
     *
     * @param MaterialCleanCodes $barcode
     * @return MaterialCleanMaterials
     */
    public function addBarcode(MaterialCleanCodes $barcode)
    {
        $this->barcode[] = $barcode;

        return $this;
    }

    /**
     * Remove barcode
     *
     * @param MaterialCleanCodes $barcode
     */
    public function removeBarcode(MaterialCleanCodes $barcode)
    {
        $this->barcode->removeElement($barcode);
    }

    /**
     * Get barcode
     *
     * @return Collection
     */
    public function getBarcode()
    {
        return $this->barcode;
    }

    /**
     * Add cleans
     *
     * @param \Nononsense\HomeBundle\Entity\MaterialCleanCleans $cleans
     * @return MaterialCleanMaterials
     */
    public function addClean(\Nononsense\HomeBundle\Entity\MaterialCleanCleans $cleans)
    {
        $this->cleans[] = $cleans;

        return $this;
    }

    /**
     * Remove cleans
     *
     * @param \Nononsense\HomeBundle\Entity\MaterialCleanCleans $cleans
     */
    public function removeClean(\Nononsense\HomeBundle\Entity\MaterialCleanCleans $cleans)
    {
        $this->cleans->removeElement($cleans);
    }

    /**
     * Get cleans
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getCleans()
    {
        return $this->cleans;
    }

    /**
     * Set product
     *
     * @param \Nononsense\HomeBundle\Entity\MaterialCleanProducts $product
     * @return MaterialCleanMaterials
     */
    public function setProduct(\Nononsense\HomeBundle\Entity\MaterialCleanProducts $product = null)
    {
        $this->product = $product;

        return $this;
    }

    /**
     * Get product
     *
     * @return \Nononsense\HomeBundle\Entity\MaterialCleanProducts 
     */
    public function getProduct()
    {
        return $this->product;
    }

    /**
     * Set additionalInfo
     *
     * @param boolean $additionalInfo
     * @return MaterialCleanMaterials
     */
    public function setAdditionalInfo($additionalInfo)
    {
        $this->additionalInfo = ($additionalInfo)?: false;

        return $this;
    }

    /**
     * Get additionalInfo
     *
     * @return boolean 
     */
    public function getAdditionalInfo()
    {
        return $this->additionalInfo;
    }

    /**
     * Set otherName
     *
     * @param boolean $otherName
     * @return MaterialCleanMaterials
     */
    public function setOtherName($otherName)
    {
        $this->otherName = $otherName;

        return $this;
    }

    /**
     * Get otherName
     *
     * @return boolean 
     */
    public function getOtherName()
    {
        return $this->otherName;
    }
}
