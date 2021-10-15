<?php

namespace Nononsense\HomeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity
 * @ORM\Table(name="cv_second_workflow_states")
 * @ORM\Entity(repositoryClass="Nononsense\HomeBundle\Entity\CVSecondWorkflowStatesRepository")
 * @ORM\HasLifecycleCallbacks
 */
class CVSecondWorkflowStates
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
     * @ORM\OneToMany(targetEntity="\Nononsense\HomeBundle\Entity\CVSecondWorkflow", mappedBy="type")
     */
    protected $cvSecondWorkflows;

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
     * Add cvSecondWorkflows
     *
     * @param \Nononsense\HomeBundle\Entity\CVSecondWorkflow $cvSecondWorkflows
     * @return CVSecondWorkflowStates
     */
    public function addCvSecondWorkflow(\Nononsense\HomeBundle\Entity\CVSecondWorkflow $cvSecondWorkflows)
    {
        $this->cvSecondWorkflows[] = $cvSecondWorkflows;

        return $this;
    }

    /**
     * Remove cvSecondWorkflows
     *
     * @param \Nononsense\HomeBundle\Entity\CVSecondWorkflow $cvSecondWorkflows
     */
    public function removeCvSecondWorkflow(\Nononsense\HomeBundle\Entity\CVSecondWorkflow $cvSecondWorkflows)
    {
        $this->cvSecondWorkflows->removeElement($cvSecondWorkflows);
    }

    /**
     * Get cvSecondWorkflows
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getCvSecondWorkflows()
    {
        return $this->cvSecondWorkflows;
    }
}
