<?php

namespace Nononsense\HomeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity
 * @ORM\Table(name="areas")
 * @ORM\Entity(repositoryClass="Nononsense\HomeBundle\Entity\AreasRepository")
 * @ORM\HasLifecycleCallbacks
 */
class Areas
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({"detail_area","list_area"})
     */
    protected $id;

    /**
     * @ORM\OneToMany(targetEntity="\Nononsense\HomeBundle\Entity\AreasUsers", mappedBy="area")
     */
    protected $users;
    
    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=200)
     * @Assert\NotBlank(message = "You shoud insert a name")
     * @Groups({"detail_area","list_area"})
     */
    protected $name;

    /**
     * @var boolean $isActive
     *
     * @ORM\Column(name="is_active", type="boolean",  nullable=true, options={"default" = false})
     * @Groups({"detail_area","list_area"})
     */
    protected $isActive;

    /**
     * @ORM\Column(type="date")
     */
    protected $created;

    /**
     * @ORM\OneToMany(targetEntity="\Nononsense\HomeBundle\Entity\TMTemplates", mappedBy="area")
     */
    protected $tmTemplates;



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
     * @return Master_Workflows
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
     * Set isActive
     *
     * @param boolean $isActive
     * @return Master_Workflows
     */
    public function setIsActive($isActive)
    {
        $this->isActive = $isActive;

        return $this;
    }

    /**
     * Get isActive
     *
     * @return boolean 
     */
    public function getIsActive()
    {
        return $this->isActive;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     * @return Master_Workflows
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
     * Add users
     *
     * @param \Nononsense\GroupBundle\Entity\AreasUsers $users
     * @return Areas
     */
    public function addUser(\Nononsense\GroupBundle\Entity\AreasUsers $users)
    {
        $this->users[] = $users;

        return $this;
    }

    /**
     * Remove users
     *
     * @param \Nononsense\GroupBundle\Entity\AreasUsers $users
     */
    public function removeUser(\Nononsense\GroupBundle\Entity\AreasUsers $users)
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

    /**
     * Add mtTemplates
     *
     * @param \Nononsense\HomeBundle\Entity\MTTemplates $mtTemplates
     * @return Areas
     */
    public function addMtTemplate(\Nononsense\HomeBundle\Entity\MTTemplates $mtTemplates)
    {
        $this->mtTemplates[] = $mtTemplates;

        return $this;
    }

    /**
     * Remove mtTemplates
     *
     * @param \Nononsense\HomeBundle\Entity\MTTemplates $mtTemplates
     */
    public function removeMtTemplate(\Nononsense\HomeBundle\Entity\MTTemplates $mtTemplates)
    {
        $this->mtTemplates->removeElement($mtTemplates);
    }

    /**
     * Get mtTemplates
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getMtTemplates()
    {
        return $this->mtTemplates;
    }

    /**
     * Add tmTemplates
     *
     * @param \Nononsense\HomeBundle\Entity\TMTemplates $tmTemplates
     * @return Areas
     */
    public function addTmTemplate(\Nononsense\HomeBundle\Entity\TMTemplates $tmTemplates)
    {
        $this->tmTemplates[] = $tmTemplates;

        return $this;
    }

    /**
     * Remove tmTemplates
     *
     * @param \Nononsense\HomeBundle\Entity\TMTemplates $tmTemplates
     */
    public function removeTmTemplate(\Nononsense\HomeBundle\Entity\TMTemplates $tmTemplates)
    {
        $this->tmTemplates->removeElement($tmTemplates);
    }

    /**
     * Get tmTemplates
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getTmTemplates()
    {
        return $this->tmTemplates;
    }
}
