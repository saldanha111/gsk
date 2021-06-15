<?php

namespace Nononsense\HomeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity
 * @ORM\Table(name="cv_states")
 * @ORM\Entity(repositoryClass="Nononsense\HomeBundle\Entity\CVStatesRepository")
 * @ORM\HasLifecycleCallbacks
 */
class CVStates
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
     * @ORM\ManyToOne(targetEntity="\Nononsense\HomeBundle\Entity\TMCumplimentationsType", inversedBy="cvStates")
     * @ORM\JoinColumn(name="type_id", referencedColumnName="id", nullable=true)
     */
    protected $type;

    /**
     * @ORM\OneToMany(targetEntity="\Nononsense\HomeBundle\Entity\CVRecords", mappedBy="state")
     */
    protected $cvRecords;

    /**
     * @ORM\OneToMany(targetEntity="\Nononsense\HomeBundle\Entity\CVActions", mappedBy="nextState")
     */
    protected $cvActions;

    /**
     * @var boolean 
     *
     * @ORM\Column(name="final", type="boolean",  options={"default" = false}, nullable=true)
     */
    protected $final;

    /**
     * @var boolean 
     *
     * @ORM\Column(name="can_be_opened", type="boolean",  options={"default" = false}, nullable=true)
     */
    protected $canBeOpened;

    /**
     * @var string
     *
     * @ORM\Column(name="icon", type="string", length=45,  nullable=true)
     */
    protected $icon;

    /**
     * @var string
     *
     * @ORM\Column(name="color", type="string", length=45,  nullable=true)
     */
    protected $color;

    /**
     * @var string
     *
     * @ORM\Column(name="name_alternative", type="string", length=255,  nullable=true)
     */
    protected $nameAlternative;

    /**
     * @var string
     *
     * @ORM\Column(name="name_reconc", type="string", length=255,  nullable=true)
     */
    protected $nameReconc;

    /**
     * @var string
     *
     * @ORM\Column(name="name_alternative_reconc", type="string", length=255,  nullable=true)
     */
    protected $nameAlternativeReconc;

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
     * Add cvSignatures
     *
     * @param \Nononsense\HomeBundle\Entity\CVSignatures $cvSignatures
     * @return CVActions
     */
    public function addCvSignature(\Nononsense\HomeBundle\Entity\CVSignatures $cvSignatures)
    {
        $this->cvSignatures[] = $cvSignatures;

        return $this;
    }

    /**
     * Remove cvSignatures
     *
     * @param \Nononsense\HomeBundle\Entity\CVSignatures $cvSignatures
     */
    public function removeCvSignature(\Nononsense\HomeBundle\Entity\CVSignatures $cvSignatures)
    {
        $this->cvSignatures->removeElement($cvSignatures);
    }

    /**
     * Get cvSignatures
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getCvSignatures()
    {
        return $this->cvSignatures;
    }

    /**
     * Add cvRecords
     *
     * @param \Nononsense\HomeBundle\Entity\CVRecords $cvRecords
     * @return CVStates
     */
    public function addCvRecord(\Nononsense\HomeBundle\Entity\CVRecords $cvRecords)
    {
        $this->cvRecords[] = $cvRecords;

        return $this;
    }

    /**
     * Remove cvRecords
     *
     * @param \Nononsense\HomeBundle\Entity\CVRecords $cvRecords
     */
    public function removeCvRecord(\Nononsense\HomeBundle\Entity\CVRecords $cvRecords)
    {
        $this->cvRecords->removeElement($cvRecords);
    }

    /**
     * Get cvRecords
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getCvRecords()
    {
        return $this->cvRecords;
    }

    /**
     * Add cvActions
     *
     * @param \Nononsense\HomeBundle\Entity\CVActions $cvActions
     * @return CVStates
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
     * Set final
     *
     * @param boolean $final
     * @return CVStates
     */
    public function setFinal($final)
    {
        $this->final = $final;

        return $this;
    }

    /**
     * Get final
     *
     * @return boolean 
     */
    public function getFinal()
    {
        return $this->final;
    }

    /**
     * Set canBeOpened
     *
     * @param boolean $canBeOpened
     * @return CVStates
     */
    public function setCanBeOpened($canBeOpened)
    {
        $this->canBeOpened = $canBeOpened;

        return $this;
    }

    /**
     * Get canBeOpened
     *
     * @return boolean 
     */
    public function getCanBeOpened()
    {
        return $this->canBeOpened;
    }

    /**
     * Set type
     *
     * @param \Nononsense\HomeBundle\Entity\TMCumplimentationsType $type
     * @return CVStates
     */
    public function setType(\Nononsense\HomeBundle\Entity\TMCumplimentationsType $type = null)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return \Nononsense\HomeBundle\Entity\TMCumplimentationsType 
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set icon
     *
     * @param string $icon
     * @return CVStates
     */
    public function setIcon($icon)
    {
        $this->icon = $icon;

        return $this;
    }

    /**
     * Get icon
     *
     * @return string 
     */
    public function getIcon()
    {
        return $this->icon;
    }

    /**
     * Set color
     *
     * @param string $color
     * @return CVStates
     */
    public function setColor($color)
    {
        $this->color = $color;

        return $this;
    }

    /**
     * Get color
     *
     * @return string 
     */
    public function getColor()
    {
        return $this->color;
    }

    /**
     * Set nameAlternative
     *
     * @param string $nameAlternative
     * @return CVStates
     */
    public function setNameAlternative($nameAlternative)
    {
        $this->nameAlternative = $nameAlternative;

        return $this;
    }

    /**
     * Get nameAlternative
     *
     * @return string 
     */
    public function getNameAlternative()
    {
        return $this->nameAlternative;
    }

    /**
     * Set nameReconc
     *
     * @param string $nameReconc
     * @return CVStates
     */
    public function setNameReconc($nameReconc)
    {
        $this->nameReconc = $nameReconc;

        return $this;
    }

    /**
     * Get nameReconc
     *
     * @return string 
     */
    public function getNameReconc()
    {
        return $this->nameReconc;
    }

    /**
     * Set nameAlternativeReconc
     *
     * @param string $nameAlternativeReconc
     * @return CVStates
     */
    public function setNameAlternativeReconc($nameAlternativeReconc)
    {
        $this->nameAlternativeReconc = $nameAlternativeReconc;

        return $this;
    }

    /**
     * Get nameAlternativeReconc
     *
     * @return string 
     */
    public function getNameAlternativeReconc()
    {
        return $this->nameAlternativeReconc;
    }
}
