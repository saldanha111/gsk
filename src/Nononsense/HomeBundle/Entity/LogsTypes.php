<?php

namespace Nononsense\HomeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity
 * @ORM\Table(name="logs_types", indexes={@ORM\Index(columns={"string_id"})})
 * @ORM\Entity(repositoryClass="Nononsense\HomeBundle\Entity\LogsTypesRepository")
 * @ORM\HasLifecycleCallbacks
 */
class LogsTypes
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
     * @ORM\Column(name="string_id", type="string", length=10)
     */
    protected $stringId;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    protected $name;

    /**
     * @var integer
     *
     * @ORM\Column(name="visible", type="integer", options={"default":0})
     */
    protected $visible;

    /**
     * @ORM\OneToMany(targetEntity="\Nononsense\HomeBundle\Entity\Logs", mappedBy="type")
     */
    protected $Logs;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->Logs = new \Doctrine\Common\Collections\ArrayCollection();
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
     * @return LogsTypes
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
     * Set visible
     *
     * @param integer $visible
     * @return LogsTypes
     */
    public function setVisible($visible)
    {
        $this->visible = $visible;

        return $this;
    }

    /**
     * Get visible
     *
     * @return integer 
     */
    public function getVisible()
    {
        return $this->visible;
    }

    /**
     * Add Logs
     *
     * @param \Nononsense\HomeBundle\Entity\Logs $logs
     * @return LogsTypes
     */
    public function addLog(\Nononsense\HomeBundle\Entity\Logs $logs)
    {
        $this->Logs[] = $logs;

        return $this;
    }

    /**
     * Remove Logs
     *
     * @param \Nononsense\HomeBundle\Entity\Logs $logs
     */
    public function removeLog(\Nononsense\HomeBundle\Entity\Logs $logs)
    {
        $this->Logs->removeElement($logs);
    }

    /**
     * Get Logs
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getLogs()
    {
        return $this->Logs;
    }
}
