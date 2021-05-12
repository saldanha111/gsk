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
     * @ORM\OneToMany(targetEntity="\Nononsense\HomeBundle\Entity\CVRecords", mappedBy="state")
     */
    protected $cvRecords;

    /**
     * @ORM\OneToMany(targetEntity="\Nononsense\HomeBundle\Entity\CVActions", mappedBy="nextState")
     */
    protected $cvActions;

    

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
}
