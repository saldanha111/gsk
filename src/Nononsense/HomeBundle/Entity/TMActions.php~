<?php

namespace Nononsense\HomeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity
 * @ORM\Table(name="tm_actions")
 * @ORM\Entity(repositoryClass="Nononsense\HomeBundle\Entity\TMActionsRepository")
 * @ORM\HasLifecycleCallbacks
 */
class TMActions
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
     * @ORM\Column(name="permission", type="string", length=255,  nullable=true)
     */
    protected $permission;

    /**
     * @ORM\OneToMany(targetEntity="\Nononsense\HomeBundle\Entity\TMSignatures", mappedBy="action")
     */
    protected $tmSignatures;

    /**
     * @ORM\OneToMany(targetEntity="\Nononsense\HomeBundle\Entity\TMWorkflow", mappedBy="action")
     */
    protected $tmWorkflows;



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
     * Set permission
     *
     * @param string $permission
     * @return TMActions
     */
    public function setPermission($permission)
    {
        $this->permission = $permission;

        return $this;
    }

    /**
     * Get permission
     *
     * @return string 
     */
    public function getPermission()
    {
        return $this->permission;
    }

    /**
     * Add tmSignatures
     *
     * @param \Nononsense\HomeBundle\Entity\TMSignatures $tmSignatures
     * @return TMActions
     */
    public function addTmSignature(\Nononsense\HomeBundle\Entity\TMSignatures $tmSignatures)
    {
        $this->tmSignatures[] = $tmSignatures;

        return $this;
    }

    /**
     * Remove tmSignatures
     *
     * @param \Nononsense\HomeBundle\Entity\TMSignatures $tmSignatures
     */
    public function removeTmSignature(\Nononsense\HomeBundle\Entity\TMSignatures $tmSignatures)
    {
        $this->tmSignatures->removeElement($tmSignatures);
    }

    /**
     * Get tmSignatures
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getTmSignatures()
    {
        return $this->tmSignatures;
    }

    /**
     * Add tmWorkflows
     *
     * @param \Nononsense\HomeBundle\Entity\TMWorkflow $tmWorkflows
     * @return TMActions
     */
    public function addTmWorkflow(\Nononsense\HomeBundle\Entity\TMWorkflow $tmWorkflows)
    {
        $this->tmWorkflows[] = $tmWorkflows;

        return $this;
    }

    /**
     * Remove tmWorkflows
     *
     * @param \Nononsense\HomeBundle\Entity\TMWorkflow $tmWorkflows
     */
    public function removeTmWorkflow(\Nononsense\HomeBundle\Entity\TMWorkflow $tmWorkflows)
    {
        $this->tmWorkflows->removeElement($tmWorkflows);
    }

    /**
     * Get tmWorkflows
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getTmWorkflows()
    {
        return $this->tmWorkflows;
    }
}
