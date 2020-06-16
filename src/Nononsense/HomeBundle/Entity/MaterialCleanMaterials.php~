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

    public function __construct()
    {
        $this->created = new DateTime();
        $this->expirationDays = 30;
        $this->active = 1;
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
}
