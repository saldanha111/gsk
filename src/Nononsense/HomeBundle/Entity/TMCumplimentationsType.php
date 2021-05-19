<?php

namespace Nononsense\HomeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity
 * @ORM\Table(name="tm_cumplimentations_type")
 * @ORM\Entity(repositoryClass="Nononsense\HomeBundle\Entity\TMCumplimentationsTypeRepository")
 * @ORM\HasLifecycleCallbacks
 */
class TMCumplimentationsType
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
     * @ORM\OneToMany(targetEntity="\Nononsense\HomeBundle\Entity\TMCumplimentations", mappedBy="tmType")
     */
    protected $tmCumplimentations;

    /**
     * @ORM\OneToMany(targetEntity="\Nononsense\HomeBundle\Entity\CVActions", mappedBy="type")
     */
    protected $cvActions;

    /**
     * @ORM\OneToMany(targetEntity="\Nononsense\HomeBundle\Entity\CVStates", mappedBy="type")
     */
    protected $cvStates;

    public function __construct()
    {

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
     * Add tmCumplimentations
     *
     * @param \Nononsense\HomeBundle\Entity\TMCumplimentations $tmCumplimentations
     * @return TMCumplimentationsType
     */
    public function addTmCumplimentation(\Nononsense\HomeBundle\Entity\TMCumplimentations $tmCumplimentations)
    {
        $this->tmCumplimentations[] = $tmCumplimentations;

        return $this;
    }

    /**
     * Remove tmCumplimentations
     *
     * @param \Nononsense\HomeBundle\Entity\TMCumplimentations $tmCumplimentations
     */
    public function removeTmCumplimentation(\Nononsense\HomeBundle\Entity\TMCumplimentations $tmCumplimentations)
    {
        $this->tmCumplimentations->removeElement($tmCumplimentations);
    }

    /**
     * Get tmCumplimentations
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getTmCumplimentations()
    {
        return $this->tmCumplimentations;
    }

    /**
     * Add cvActions
     *
     * @param \Nononsense\HomeBundle\Entity\CVActions $cvActions
     * @return TMCumplimentationsType
     */
    public function addCvAction(\Nononsense\HomeBundle\Entity\CVActions $cvActions)
    {
        $this->cvActions[] = $cvActions;

        return $this;
    }

    /**
     * Remove cvActions
     *
     * @param \Nononsense\HomeBundle\Entity\CVActions $cvActions
     */
    public function removeCvAction(\Nononsense\HomeBundle\Entity\CVActions $cvActions)
    {
        $this->cvActions->removeElement($cvActions);
    }

    /**
     * Get cvActions
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getCvActions()
    {
        return $this->cvActions;
    }

    /**
     * Add cvStates
     *
     * @param \Nononsense\HomeBundle\Entity\CVStates $cvStates
     * @return TMCumplimentationsType
     */
    public function addCvState(\Nononsense\HomeBundle\Entity\CVStates $cvStates)
    {
        $this->cvStates[] = $cvStates;

        return $this;
    }

    /**
     * Remove cvStates
     *
     * @param \Nononsense\HomeBundle\Entity\CVStates $cvStates
     */
    public function removeCvState(\Nononsense\HomeBundle\Entity\CVStates $cvStates)
    {
        $this->cvStates->removeElement($cvStates);
    }

    /**
     * Get cvStates
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getCvStates()
    {
        return $this->cvStates;
    }
}
