<?php

namespace Nononsense\HomeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="archive_actions")
 * @ORM\Entity(repositoryClass="Nononsense\HomeBundle\Entity\ArchiveActionsRepository")
 * @ORM\HasLifecycleCallbacks
 */
class ArchiveActions
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
     * @ORM\OneToMany(targetEntity="\Nononsense\HomeBundle\Entity\ArchiveSignatures", mappedBy="archiveAction")
     */
    protected $archiveSignatures;


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
     * @return ArchiveActions
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
     * Add archiveSignatures
     *
     * @param \Nononsense\HomeBundle\Entity\ArchiveSignatures $archiveSignatures
     * @return ArchiveActions
     */
    public function addArchiveSignature(\Nononsense\HomeBundle\Entity\ArchiveSignatures $archiveSignatures)
    {
        $this->archiveSignatures[] = $archiveSignatures;

        return $this;
    }

    /**
     * Remove archiveSignatures
     *
     * @param \Nononsense\HomeBundle\Entity\ArchiveSignatures $archiveSignatures
     */
    public function removeArchiveSignature(\Nononsense\HomeBundle\Entity\ArchiveSignatures $archiveSignatures)
    {
        $this->archiveSignatures->removeElement($archiveSignatures);
    }

    /**
     * Get archiveSignatures
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getArchiveSignatures()
    {
        return $this->archiveSignatures;
    }
}
