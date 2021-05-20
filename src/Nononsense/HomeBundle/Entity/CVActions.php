<?php

namespace Nononsense\HomeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity
 * @ORM\Table(name="cv_actions")
 * @ORM\Entity(repositoryClass="Nononsense\HomeBundle\Entity\CVActionsRepository")
 * @ORM\HasLifecycleCallbacks
 */
class CVActions
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
     * @var string
     *
     * @ORM\Column(name="name_alternative", type="string", length=255,  nullable=true)
     */
    protected $nameAlternative;


    /**
     * @ORM\ManyToOne(targetEntity="\Nononsense\HomeBundle\Entity\CVStates", inversedBy="cvActions")
     * @ORM\JoinColumn(name="next_state", referencedColumnName="id")
     */
    protected $nextState;

    /**
     * @ORM\ManyToOne(targetEntity="\Nononsense\HomeBundle\Entity\TMCumplimentationsType", inversedBy="cvActions")
     * @ORM\JoinColumn(name="type_id", referencedColumnName="id")
     */
    protected $type;

    /**
     * @ORM\OneToMany(targetEntity="\Nononsense\HomeBundle\Entity\CVSignatures", mappedBy="action")
     */
    protected $cvSignatures;

    /**
     * @var boolean 
     *
     * @ORM\Column(name="justification", type="boolean",  options={"default" = false}, nullable=true)
     */
    protected $justification;

    

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
     * Set nextState
     *
     * @param \Nononsense\HomeBundle\Entity\CVStates $nextState
     * @return CVActions
     */
    public function setNextState(\Nononsense\HomeBundle\Entity\CVStates $nextState = null)
    {
        $this->nextState = $nextState;

        return $this;
    }

    /**
     * Get nextState
     *
     * @return \Nononsense\HomeBundle\Entity\CVStates 
     */
    public function getNextState()
    {
        return $this->nextState;
    }

    /**
     * Set type
     *
     * @param \Nononsense\HomeBundle\Entity\TMCumplimentationsType $type
     * @return CVActions
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
     * Set justification
     *
     * @param boolean $justification
     * @return CVActions
     */
    public function setJustification($justification)
    {
        $this->justification = $justification;

        return $this;
    }

    /**
     * Get justification
     *
     * @return boolean 
     */
    public function getJustification()
    {
        return $this->justification;
    }

    /**
     * Set nameAlternative
     *
     * @param string $nameAlternative
     * @return CVActions
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
}
