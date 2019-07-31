<?php

namespace Nononsense\NotificationsBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity
 * @ORM\Table(name="messages_users")
 * @ORM\Entity(repositoryClass="Nononsense\NotificationsBundle\Entity\MessagesUsersRepository")
 * @ORM\HasLifecycleCallbacks
 */
class MessagesUsers
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    
    /**
     * @ORM\ManyToOne(targetEntity="\Nononsense\UserBundle\Entity\Users", inversedBy="messages")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    protected $user;
    
    /**
     * @ORM\ManyToOne(targetEntity="\Nononsense\NotificationsBundle\Entity\Notifications", inversedBy="messages")
     * @ORM\JoinColumn(name="notification_id", referencedColumnName="id")
     */
    protected $notification;
    
    /**
     * @ORM\Column(name="trash", type="boolean", options={"default": false})
     */
    protected $trash;
    
    /**
     * @ORM\Column(type="date")
     */
    protected $created;
    
    /**
     * @ORM\Column(type="date")
     */
    protected $modified;
    
    
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
     * Set subject
     *
     * @param string $subject
     * @return MessagesUsers
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;

        return $this;
    }

    /**
     * Get subject
     *
     * @return string 
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * Set isActive
     *
     * @param boolean $isActive
     * @return MessagesUsers
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
     * Set body
     *
     * @param string $body
     * @return MessagesUsers
     */
    public function setBody($body)
    {
        $this->body = $body;

        return $this;
    }

    /**
     * Get body
     *
     * @return string 
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * Set trash
     *
     * @param boolean $trash
     * @return MessagesUsers
     */
    public function setTrash($trash)
    {
        $this->trash = $trash;

        return $this;
    }

    /**
     * Get trash
     *
     * @return boolean 
     */
    public function getTrash()
    {
        return $this->trash;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     * @return MessagesUsers
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
     * @return MessagesUsers
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
     * Set user
     *
     * @param \Nononsense\UserBundle\Entity\Users $user
     * @return MessagesUsers
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
     * Set notification
     *
     * @param \Nononsense\NotificationsBundle\Entity\Notifications $notification
     * @return MessagesUsers
     */
    public function setNotification(\Nononsense\NotificationsBundle\Entity\Notifications $notification = null)
    {
        $this->notification = $notification;

        return $this;
    }

    /**
     * Get notification
     *
     * @return \Nononsense\NotificationsBundle\Entity\Notifications 
     */
    public function getNotification()
    {
        return $this->notification;
    }
}
