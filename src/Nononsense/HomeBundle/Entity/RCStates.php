<?php

namespace Nononsense\HomeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="rc_states")
 * @ORM\Entity(repositoryClass="Nononsense\HomeBundle\Entity\RCStatesRepository")
 * @ORM\HasLifecycleCallbacks
 */
class RCStates
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
     * @ORM\OneToMany(targetEntity="\Nononsense\HomeBundle\Entity\RetentionCategories", mappedBy="documentState")
     */
    protected $category;

    /**
     * @ORM\ManyToOne(targetEntity="\Nononsense\HomeBundle\Entity\RCTypes", inversedBy="state")
     * @ORM\JoinColumn(name="type", referencedColumnName="id")
     */
    protected $type;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->category = new \Doctrine\Common\Collections\ArrayCollection();
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
     * @return RCStates
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
     * Add category
     *
     * @param \Nononsense\HomeBundle\Entity\RetentionCategories $category
     * @return RCStates
     */
    public function addCategory(\Nononsense\HomeBundle\Entity\RetentionCategories $category)
    {
        $this->category[] = $category;

        return $this;
    }

    /**
     * Remove category
     *
     * @param \Nononsense\HomeBundle\Entity\RetentionCategories $category
     */
    public function removeCategory(\Nononsense\HomeBundle\Entity\RetentionCategories $category)
    {
        $this->category->removeElement($category);
    }

    /**
     * Get category
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * Set type
     *
     * @param \Nononsense\HomeBundle\Entity\RCTypes $type
     * @return RCStates
     */
    public function setType(\Nononsense\HomeBundle\Entity\RCTypes $type = null)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return \Nononsense\HomeBundle\Entity\RCTypes 
     */
    public function getType()
    {
        return $this->type;
    }
}
