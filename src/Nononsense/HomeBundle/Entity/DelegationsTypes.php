<?php
namespace Nononsense\HomeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity
 * @ORM\Table(name="delegations_types")
 * @ORM\Entity(repositoryClass="Nononsense\HomeBundle\Entity\DelegationsTypesRepository")
 * @ORM\HasLifecycleCallbacks
 */
class DelegationsTypes
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\OneToMany(targetEntity="\Nononsense\HomeBundle\Entity\Delegations", mappedBy="type")
     */
    protected $delegations;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255,  nullable=true)
     */
    protected $name;

    
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
     * @return DelegationsTypes
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
     * Add delegations
     *
     * @param \Nononsense\HomeBundle\Entity\Delegations $delegations
     * @return DelegationsTypes
     */
    public function addDelegation(\Nononsense\HomeBundle\Entity\Delegations $delegations)
    {
        $this->delegations[] = $delegations;

        return $this;
    }

    /**
     * Remove delegations
     *
     * @param \Nononsense\HomeBundle\Entity\Delegations $delegations
     */
    public function removeDelegation(\Nononsense\HomeBundle\Entity\Delegations $delegations)
    {
        $this->delegations->removeElement($delegations);
    }

    /**
     * Get delegations
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getDelegations()
    {
        return $this->delegations;
    }
}
