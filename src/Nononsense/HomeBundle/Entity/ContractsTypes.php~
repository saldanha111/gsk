<?php

namespace Nononsense\HomeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity
 * @ORM\Table(name="contracts_types")
 * @ORM\Entity(repositoryClass="Nononsense\HomeBundle\Entity\ContractsTypesRepository")
 * @ORM\HasLifecycleCallbacks
 */
class ContractsTypes
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({"detail_contract"})
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=90)
     * @Groups({"detail_contract"})
     */
    protected $name;

    /**
     * @ORM\Column(type="date")
     */
    protected $created;

    /**
     * @ORM\Column(type="date")
     */
    protected $modified;

    /**
     * @ORM\OneToMany(targetEntity="\Nononsense\HomeBundle\Entity\Contracts", mappedBy="type")
     */
    protected $Contracts;

    /**
     * @ORM\OneToMany(targetEntity="\Nononsense\HomeBundle\Entity\RecordsContracts", mappedBy="type")
     */
    protected $records;

    public function __construct()
    {

    }

    /**
     * @ORM\PrePersist
     */
    public function setCreatedValue()
    {
        if (!$this->created) {
            $this->created = new \DateTime();
        }
        $this->modified = $this->created;
    }

    /**
     * @ORM\PreUpdate
     */
    public function setModifiedValue()
    {
        $this->modified = new \DateTime();
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
     * @return Categories
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
     * Set created
     *
     * @param \DateTime $created
     * @return Categories
     */
    public function setCreated($created)
    {
        $this->created = $created;

        return $this;
    }

    /**
     * Get created
     *
     * @return \DateTime 
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Set modified
     *
     * @param \DateTime $modified
     * @return Categories
     */
    public function setModified($modified)
    {
        $this->modified = $modified;

        return $this;
    }

    /**
     * Get modified
     *
     * @return \DateTime 
     */
    public function getModified()
    {
        return $this->modified;
    }

    /**
     * Add contracts
     *
     * @param \Nononsense\HomeBundle\Entity\Documents $contracts
     * @return Types
     */
    public function addDocument(\Nononsense\HomeBundle\Entity\Documents $contracts)
    {
        $this->contracts[] = $contracts;

        return $this;
    }

    /**
     * Remove contracts
     *
     * @param \Nononsense\HomeBundle\Entity\Documents $contracts
     */
    public function removeDocument(\Nononsense\HomeBundle\Entity\Documents $contracts)
    {
        $this->contracts->removeElement($contracts);
    }

    /**
     * Get contracts
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getDocuments()
    {
        return $this->contracts;
    }

    /**
     * Add records
     *
     * @param \Nononsense\HomeBundle\Entity\RecordsDocuments $records
     * @return Types
     */
    public function addRecord(\Nononsense\HomeBundle\Entity\RecordsDocuments $records)
    {
        $this->records[] = $records;

        return $this;
    }

    /**
     * Remove records
     *
     * @param \Nononsense\HomeBundle\Entity\RecordsDocuments $records
     */
    public function removeRecord(\Nononsense\HomeBundle\Entity\RecordsDocuments $records)
    {
        $this->records->removeElement($records);
    }

    /**
     * Get records
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getRecords()
    {
        return $this->records;
    }

    /**
     * Add Contracts
     *
     * @param \Nononsense\HomeBundle\Entity\Contracts $contracts
     * @return ContractsTypes
     */
    public function addContract(\Nononsense\HomeBundle\Entity\Contracts $contracts)
    {
        $this->Contracts[] = $contracts;

        return $this;
    }

    /**
     * Remove Contracts
     *
     * @param \Nononsense\HomeBundle\Entity\Contracts $contracts
     */
    public function removeContract(\Nononsense\HomeBundle\Entity\Contracts $contracts)
    {
        $this->Contracts->removeElement($contracts);
    }

    /**
     * Get Contracts
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getContracts()
    {
        return $this->Contracts;
    }
}
