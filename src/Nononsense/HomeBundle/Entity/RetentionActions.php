<?php

namespace Nononsense\HomeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="retention_actions")
 * @ORM\Entity(repositoryClass="Nononsense\HomeBundle\Entity\RetentionActionsRepository")
 * @ORM\HasLifecycleCallbacks
 */
class RetentionActions
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255,  nullable=true)
     */
    protected $name;

    /**
     * @ORM\OneToMany(targetEntity="\Nononsense\HomeBundle\Entity\RetentionSignatures", mappedBy="retentionAction")
     */
    protected $retentionSignatures;


    /**
     * Constructor
     */
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
     * @return RetentionActions
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
     * Add retentionSignatures
     *
     * @param \Nononsense\HomeBundle\Entity\RetentionSignatures $retentionSignatures
     * @return RetentionActions
     */
    public function addRetentionSignature(\Nononsense\HomeBundle\Entity\RetentionSignatures $retentionSignatures)
    {
        $this->retentionSignatures[] = $retentionSignatures;

        return $this;
    }

    /**
     * Remove retentionSignatures
     *
     * @param \Nononsense\HomeBundle\Entity\RetentionSignatures $retentionSignatures
     */
    public function removeRetentionSignature(\Nononsense\HomeBundle\Entity\RetentionSignatures $retentionSignatures)
    {
        $this->retentionSignatures->removeElement($retentionSignatures);
    }

    /**
     * Get retentionSignatures
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getRetentionSignatures()
    {
        return $this->retentionSignatures;
    }
}
