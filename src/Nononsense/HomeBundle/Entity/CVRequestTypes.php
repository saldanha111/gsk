<?php

namespace Nononsense\HomeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity
 * @ORM\Table(name="cv_request_types")
 * @ORM\Entity(repositoryClass="Nononsense\HomeBundle\Entity\CVRequestTypesRepository")
 * @ORM\HasLifecycleCallbacks
 */
class CVRequestTypes
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
     * @ORM\OneToMany(targetEntity="\Nononsense\HomeBundle\Entity\CVRecords", mappedBy="requestType")
     */
    protected $cvRecords;

    /**
     * @var boolean 
     *
     * @ORM\Column(name="requireFll", type="boolean",  options={"default" = false}, nullable=true)
     */
    protected $require_fll;

    /**
     * @var boolean 
     *
     * @ORM\Column(name="requireEco", type="boolean",  options={"default" = false}, nullable=true)
     */
    protected $require_eco;

    /**
     * @var boolean 
     *
     * @ORM\Column(name="add_verifiers_fll", type="boolean",  options={"default" = false}, nullable=true)
     */
    protected $addVerifiersFll;

    /**
     * @var boolean 
     *
     * @ORM\Column(name="add_verifiers_eco", type="boolean",  options={"default" = false}, nullable=true)
     */
    protected $addVerifiersEco;

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
     * Set require_eco
     *
     * @param boolean $requireEco
     * @return CVRequestTypes
     */
    public function setRequireEco($requireEco)
    {
        $this->require_eco = $requireEco;

        return $this;
    }

    /**
     * Get require_eco
     *
     * @return boolean 
     */
    public function getRequireEco()
    {
        return $this->require_eco;
    }

    /**
     * Add cvRecords
     *
     * @param \Nononsense\HomeBundle\Entity\CVRecords $cvRecords
     * @return CVRequestTypes
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
     * Set require_fll
     *
     * @param boolean $requireFll
     * @return CVRequestTypes
     */
    public function setRequireFll($requireFll)
    {
        $this->require_fll = $requireFll;

        return $this;
    }

    /**
     * Get require_fll
     *
     * @return boolean 
     */
    public function getRequireFll()
    {
        return $this->require_fll;
    }

    /**
     * Set addVerifiersFll
     *
     * @param boolean $addVerifiersFll
     * @return CVRequestTypes
     */
    public function setAddVerifiersFll($addVerifiersFll)
    {
        $this->addVerifiersFll = $addVerifiersFll;

        return $this;
    }

    /**
     * Get addVerifiersFll
     *
     * @return boolean 
     */
    public function getAddVerifiersFll()
    {
        return $this->addVerifiersFll;
    }

    /**
     * Set addVerifiersEco
     *
     * @param boolean $addVerifiersEco
     * @return CVRequestTypes
     */
    public function setAddVerifiersEco($addVerifiersEco)
    {
        $this->addVerifiersEco = $addVerifiersEco;

        return $this;
    }

    /**
     * Get addVerifiersEco
     *
     * @return boolean 
     */
    public function getAddVerifiersEco()
    {
        return $this->addVerifiersEco;
    }
}
