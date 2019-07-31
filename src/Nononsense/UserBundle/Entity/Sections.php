<?php
/**
 * Created by IntelliJ IDEA.
 * User: gushe
 * Date: 26/06/2018
 * Time: 13:03
 */

namespace Nononsense\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity
 * @ORM\Table(name="sections")
 * @ORM\Entity(repositoryClass="Nononsense\UserBundle\Entity\SectionsRepository")
 * @ORM\HasLifecycleCallbacks
 */
class Sections
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="date")
     */
    protected $created;

    /**
     * @ORM\Column(type="date")
     */
    protected $modified;

    /**
     * @ORM\Column(type="integer")
     */
    protected $idsection;

    /**
     * @ORM\Column(name="name", type="string", length=90)
     */
    protected $name;

    /**
     * @ORM\OneToMany(targetEntity="\Nononsense\UserBundle\Entity\UsersSection", mappedBy="section")
     */
    protected $users;

    /**
     * Sections constructor.
     */

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
     * Set created
     *
     * @param \DateTime $created
     * @return Sections
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
     * @return Sections
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
     * Set idsection
     *
     * @param integer $idsection
     * @return Sections
     */
    public function setIdsection($idsection)
    {
        $this->idsection = $idsection;

        return $this;
    }

    /**
     * Get idsection
     *
     * @return integer 
     */
    public function getIdsection()
    {
        return $this->idsection;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return Sections
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
     * Add users
     *
     * @param \Nononsense\UserBundle\Entity\UsersSection $users
     * @return Sections
     */
    public function addUser(\Nononsense\UserBundle\Entity\UsersSection $users)
    {
        $this->users[] = $users;

        return $this;
    }

    /**
     * Remove users
     *
     * @param \Nononsense\UserBundle\Entity\UsersSection $users
     */
    public function removeUser(\Nononsense\UserBundle\Entity\UsersSection $users)
    {
        $this->users->removeElement($users);
    }

    /**
     * Get users
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getUsers()
    {
        return $this->users;
    }
}
