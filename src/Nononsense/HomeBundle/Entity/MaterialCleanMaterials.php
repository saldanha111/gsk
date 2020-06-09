<?php

namespace Nononsense\HomeBundle\Entity;

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
     * @var integer
     *
     * @ORM\Column(name="expiration_days", type="integer")
     */
    protected $expirationDays;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $created;

    /**
     * @var boolean
     *
     * @ORM\Column(name="active", type="boolean")
     */
    private $active;

    public function __construct()
    {
        $this->created = new \DateTime();
        $this->expirationDays = 30;
        $this->active = 1;
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
     * @param integer $expirationDays
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
     * @return integer 
     */
    public function getExpirationDays()
    {
        return $this->expirationDays;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
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
     * @return boolean 
     */
    public function getActive()
    {
        return $this->active;
    }
}
