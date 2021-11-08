<?php

namespace Nononsense\HomeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity
 * @ORM\Table(name="tm_cumplimentations")
 * @ORM\Entity(repositoryClass="Nononsense\HomeBundle\Entity\TMCumplimentationsRepository")
 * @ORM\HasLifecycleCallbacks
 */
class TMCumplimentations
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({"group1"})
     */
    protected $id;

    /**
     * @ORM\OneToMany(targetEntity="\Nononsense\HomeBundle\Entity\CVWorkflow", mappedBy="type")
     */
    protected $cvWorkflows;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255,  nullable=true)
     */
    protected $name;

    /**
     * @ORM\ManyToOne(targetEntity="\Nononsense\HomeBundle\Entity\TMCumplimentationsType", inversedBy="tmCumplimentations")
     * @ORM\JoinColumn(name="type_id", referencedColumnName="id")
     */
    protected $tmType;

    /**
     * @ORM\OneToMany(targetEntity="\Nononsense\HomeBundle\Entity\TMSecondWorkflow", mappedBy="tmCumplimentation")
     */
    protected $tmSecondWorkflows;

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
     * Set tmType
     *
     * @param \Nononsense\HomeBundle\Entity\TMCumplimentationsType $tmType
     * @return TMCumplimentations
     */
    public function setTmType(\Nononsense\HomeBundle\Entity\TMCumplimentationsType $tmType = null)
    {
        $this->tmType = $tmType;

        return $this;
    }

    /**
     * Get tmType
     *
     * @return \Nononsense\HomeBundle\Entity\TMCumplimentationsType 
     */
    public function getTmType()
    {
        return $this->tmType;
    }

    /**
     * Add tmSecondWorkflows
     *
     * @param \Nononsense\HomeBundle\Entity\TMSecondWorkflow $tmSecondWorkflows
     * @return TMCumplimentations
     */
    public function addTmSecondWorkflow(\Nononsense\HomeBundle\Entity\TMSecondWorkflow $tmSecondWorkflows)
    {
        $this->tmSecondWorkflows[] = $tmSecondWorkflows;

        return $this;
    }

    /**
     * Remove tmSecondWorkflows
     *
     * @param \Nononsense\HomeBundle\Entity\TMSecondWorkflow $tmSecondWorkflows
     */
    public function removeTmSecondWorkflow(\Nononsense\HomeBundle\Entity\TMSecondWorkflow $tmSecondWorkflows)
    {
        $this->tmSecondWorkflows->removeElement($tmSecondWorkflows);
    }

    /**
     * Get tmSecondWorkflows
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getTmSecondWorkflows()
    {
        return $this->tmSecondWorkflows;
    }

    /**
     * Add cvWorkflows
     *
     * @param \Nononsense\HomeBundle\Entity\CVWorkflow $cvWorkflows
     * @return TMCumplimentations
     */
    public function addCvWorkflow(\Nononsense\HomeBundle\Entity\CVWorkflow $cvWorkflows)
    {
        $this->cvWorkflows[] = $cvWorkflows;

        return $this;
    }

    /**
     * Remove cvWorkflows
     *
     * @param \Nononsense\HomeBundle\Entity\CVWorkflow $cvWorkflows
     */
    public function removeCvWorkflow(\Nononsense\HomeBundle\Entity\CVWorkflow $cvWorkflows)
    {
        $this->cvWorkflows->removeElement($cvWorkflows);
    }

    /**
     * Get cvWorkflows
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getCvWorkflows()
    {
        return $this->cvWorkflows;
    }

    /**
     * Add cvSignatures
     *
     * @param \Nononsense\HomeBundle\Entity\CVSignatures $cvSignatures
     * @return TMCumplimentations
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
}
