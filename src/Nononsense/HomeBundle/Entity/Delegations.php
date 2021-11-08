<?php
namespace Nononsense\HomeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity
 * @ORM\Table(name="delegations")
 * @ORM\Entity(repositoryClass="Nononsense\HomeBundle\Entity\DelegationsRepository")
 * @ORM\HasLifecycleCallbacks
 */
class Delegations
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="\Nononsense\HomeBundle\Entity\DelegationsTypes", inversedBy="delegations")
     * @ORM\JoinColumn(name="type_id", referencedColumnName="id")
     */
    protected $type;

    /**
     * @ORM\ManyToOne(targetEntity="\Nononsense\UserBundle\Entity\Users", inversedBy="delegationsusers")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    protected $user;

    /**
     * @ORM\ManyToOne(targetEntity="\Nononsense\UserBundle\Entity\Users", inversedBy="delegationssustitutes")
     * @ORM\JoinColumn(name="sustitute_id", referencedColumnName="id")
     */
    protected $sustitute;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $created;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $deleted;


    
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
     * Set created
     *
     * @param \DateTime $created
     * @return Delegations
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
     * Set deleted
     *
     * @param \DateTime $deleted
     * @return Delegations
     */
    public function setDeleted($deleted)
    {
        $this->deleted = $deleted;

        return $this;
    }

    /**
     * Get deleted
     *
     * @return \DateTime 
     */
    public function getDeleted()
    {
        return $this->deleted;
    }

    /**
     * Set type
     *
     * @param \Nononsense\HomeBundle\Entity\DelegationsTypes $type
     * @return Delegations
     */
    public function setType(\Nononsense\HomeBundle\Entity\DelegationsTypes $type = null)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return \Nononsense\HomeBundle\Entity\DelegationsTypes 
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set user
     *
     * @param \Nononsense\UserBundle\Entity\Users $user
     * @return Delegations
     */
    public function setUser(\Nononsense\UserBundle\Entity\Users $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \Nononsense\UserBundle\Entity\Users 
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set sustitute
     *
     * @param \Nononsense\UserBundle\Entity\Users $sustitute
     * @return Delegations
     */
    public function setSustitute(\Nononsense\UserBundle\Entity\Users $sustitute = null)
    {
        $this->sustitute = $sustitute;

        return $this;
    }

    /**
     * Get sustitute
     *
     * @return \Nononsense\UserBundle\Entity\Users 
     */
    public function getSustitute()
    {
        return $this->sustitute;
    }
}
