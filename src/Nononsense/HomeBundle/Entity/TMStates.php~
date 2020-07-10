<?php

namespace Nononsense\HomeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity
 * @ORM\Table(name="tm_states")
 * @ORM\Entity(repositoryClass="Nononsense\HomeBundle\Entity\TMStatesRepository")
 * @ORM\HasLifecycleCallbacks
 */
class TMStates
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({"group1"})
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255,  nullable=true)
     */
    protected $name;

    /**
     * @ORM\OneToMany(targetEntity="\Nononsense\HomeBundle\Entity\TMTemplates", mappedBy="state")
     */
    protected $tmTemplates;



    public function __construct()
    {
        $this->fields = new ArrayCollection();
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
     * @return TMStates
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
     * Add tmTemplates
     *
     * @param \Nononsense\HomeBundle\Entity\TMTemplates $tmTemplates
     * @return MTStates
     */
    public function addTmTemplate(\Nononsense\HomeBundle\Entity\TMTemplates $tmTemplates)
    {
        $this->tmTemplates[] = $tmTemplates;

        return $this;
    }

    /**
     * Remove tmTemplates
     *
     * @param \Nononsense\HomeBundle\Entity\TMTemplates $tmTemplates
     */
    public function removeTmTemplate(\Nononsense\HomeBundle\Entity\TMTemplates $tmTemplates)
    {
        $this->tmTemplates->removeElement($tmTemplates);
    }

    /**
     * Get tmTemplates
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getTmTemplates()
    {
        return $this->tmTemplates;
    }
}
