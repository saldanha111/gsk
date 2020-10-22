<?php

namespace Nononsense\HomeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="rc_types")
 * @ORM\Entity(repositoryClass="Nononsense\HomeBundle\Entity\RCTypesRepository")
 * @ORM\HasLifecycleCallbacks
 */
class RCTypes
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
     * @ORM\OneToMany(targetEntity="\Nononsense\HomeBundle\Entity\RetentionCategories", mappedBy="type")
     */
    protected $rCategory;

    /**
     * @ORM\OneToMany(targetEntity="\Nononsense\HomeBundle\Entity\RCStates", mappedBy="type")
     */
    protected $state;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->rCategory = new \Doctrine\Common\Collections\ArrayCollection();
        $this->state = new \Doctrine\Common\Collections\ArrayCollection();
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
     * @return RCTypes
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
     * Add rCategory
     *
     * @param \Nononsense\HomeBundle\Entity\RetentionCategories $rCategory
     * @return RCTypes
     */
    public function addRCategory(\Nononsense\HomeBundle\Entity\RetentionCategories $rCategory)
    {
        $this->rCategory[] = $rCategory;

        return $this;
    }

    /**
     * Remove rCategory
     *
     * @param \Nononsense\HomeBundle\Entity\RetentionCategories $rCategory
     */
    public function removeRCategory(\Nononsense\HomeBundle\Entity\RetentionCategories $rCategory)
    {
        $this->rCategory->removeElement($rCategory);
    }

    /**
     * Get rCategory
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getRCategory()
    {
        return $this->rCategory;
    }

    /**
     * Add state
     *
     * @param \Nononsense\HomeBundle\Entity\RCStates $state
     * @return RCTypes
     */
    public function addState(\Nononsense\HomeBundle\Entity\RCStates $state)
    {
        $this->state[] = $state;

        return $this;
    }

    /**
     * Remove state
     *
     * @param \Nononsense\HomeBundle\Entity\RCStates $state
     */
    public function removeState(\Nononsense\HomeBundle\Entity\RCStates $state)
    {
        $this->state->removeElement($state);
    }

    /**
     * Get state
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getState()
    {
        return $this->state;
    }
}
