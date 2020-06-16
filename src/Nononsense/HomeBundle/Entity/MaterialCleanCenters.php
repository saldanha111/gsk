<?php

namespace Nononsense\HomeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="mc_centers")
 * @ORM\Entity(repositoryClass="Nononsense\HomeBundle\Entity\MaterialCleanCentersRepository")
 * @ORM\HasLifecycleCallbacks
 */
class MaterialCleanCenters
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
     * @var string
     *
     * @ORM\Column(name="description", type="text")
     */
    protected $description;

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

    /**
     * @ORM\OneToMany(targetEntity="\Nononsense\HomeBundle\Entity\MaterialCleanCodes", mappedBy="idCenter")
     */
    private $barcode;

    /**
     * @ORM\OneToMany(targetEntity="\Nononsense\HomeBundle\Entity\MaterialCleanCleans", mappedBy="center")
     */
    private $cleans;

    public function __construct()
    {
        $this->created = new \DateTime();
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
     * @return MaterialCleanCenters
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
     * Set description
     *
     * @param string $description
     * @return MaterialCleanCenters
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
     * Set created
     *
     * @param \DateTime $created
     * @return MaterialCleanCenters
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
     * @return MaterialCleanCenters
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

    /**
     * Add barcode
     *
     * @param \Nononsense\HomeBundle\Entity\MaterialCleanCodes $barcode
     * @return MaterialCleanCenters
     */
    public function addBarcode(\Nononsense\HomeBundle\Entity\MaterialCleanCodes $barcode)
    {
        $this->barcode[] = $barcode;

        return $this;
    }

    /**
     * Remove barcode
     *
     * @param \Nononsense\HomeBundle\Entity\MaterialCleanCodes $barcode
     */
    public function removeBarcode(\Nononsense\HomeBundle\Entity\MaterialCleanCodes $barcode)
    {
        $this->barcode->removeElement($barcode);
    }

    /**
     * Get barcode
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getBarcode()
    {
        return $this->barcode;
    }

    /**
     * Add cleans
     *
     * @param \Nononsense\HomeBundle\Entity\MaterialCleanCleans $cleans
     * @return MaterialCleanCenters
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
}
