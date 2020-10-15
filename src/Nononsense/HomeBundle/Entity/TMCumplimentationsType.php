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
}
