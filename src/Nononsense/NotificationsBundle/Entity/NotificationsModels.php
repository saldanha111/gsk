<?php

namespace Nononsense\NotificationsBundle\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Nononsense\GroupBundle\Entity\Groups;
use Nononsense\HomeBundle\Entity\CVStates;
use Nononsense\HomeBundle\Entity\TMTemplates;
use Nononsense\UserBundle\Entity\Users;

/**
 * @ORM\Entity
 * @ORM\Table(name="notifications_models")
 * @ORM\Entity(repositoryClass="Nononsense\NotificationsBundle\Entity\NotificationsModelsRepository")
 * @ORM\HasLifecycleCallbacks
 */
class NotificationsModels
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="\Nononsense\HomeBundle\Entity\TMTemplates", inversedBy="notificationsModels")
     * @ORM\JoinColumn(name="template_id", referencedColumnName="id")
     */
    protected $templateId;

    /**
     * @ORM\ManyToOne(targetEntity="\Nononsense\UserBundle\Entity\Users", inversedBy="notificationsModels")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    protected $user;

    /**
     * @ORM\ManyToOne(targetEntity="\Nononsense\GroupBundle\Entity\Groups", inversedBy="notificationsModels")
     * @ORM\JoinColumn(name="group_id", referencedColumnName="id")
     */
    protected $group;

    /**
     * @ORM\ManyToOne(targetEntity="\Nononsense\HomeBundle\Entity\CVStates", inversedBy="notificationsModels")
     * @ORM\JoinColumn(name="state_id", referencedColumnName="id")
     */
    protected $state;
    
    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=20)
     */
    protected $email;
    
    /**
     * @ORM\Column(name="body", type="text")
     */
    protected $body;
    
    /**
     * @ORM\Column(name="created_at", type="datetime")
     */
    protected $createdAt;

    /**
     * @ORM\ManyToOne(targetEntity="\Nononsense\UserBundle\Entity\Users", inversedBy="notificationsModels")
     * @ORM\JoinColumn(name="created_by", referencedColumnName="id")
     */
    protected $createdBy;

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_removed", type="boolean", options={"default" : 0})
     */
    protected $isRemoved;

    /**
     * @ORM\ManyToOne(targetEntity="\Nononsense\UserBundle\Entity\Users", inversedBy="notificationsModels")
     * @ORM\JoinColumn(name="removed_by", referencedColumnName="id")
     */
    protected $removedBy;

    /**
     * @ORM\Column(name="removed_at", type="datetime")
     */
    protected $removedAt;

    /**
     * @ORM\Column(name="subject", type="string", length=20)
     */
    protected $subject;
    

    public function __construct(){
        $this->isRemoved = false;
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
     * Get templateId
     *
     * @return TMTemplates
     */
    public function getTemplateId()
    {
        return $this->templateId;
    }

    /**
     * Set templateId
     *
     * @param TMTemplates $template
     * @return NotificationsModels
     */
    public function setTemplateId(TMTemplates $template)
    {
        $this->templateId = $template;

        return $this;
    }

    /**
     * Set user
     *
     * @param Users $user
     * @return NotificationsModels
     */
    public function setUser(Users $user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return Users
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Get group
     *
     * @return Groups
     */
    public function getGroup()
    {
        return $this->group;
    }

    /**
     * Set group
     *
     * @param Groups $group
     * @return NotificationsModels
     */
    public function setGroup(Groups $group)
    {
        $this->group = $group;

        return $this;
    }

    /**
     * Get state
     *
     * @return CVStates
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * Set state
     *
     * @param CVStates $state
     * @return NotificationsModels
     */
    public function setState(CVStates $state)
    {
        $this->state = $state;

        return $this;
    }


    /**
     * Get body
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }


    /**
     * Set email
     *
     * @param string $email
     * @return NotificationsModels
     */
    public function setEmail($email)
    {
        $this->email = $email;

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
     * Set body
     *
     * @param string $body
     * @return NotificationsModels
     */
    public function setBody($body)
    {
        $this->body = $body;

        return $this;

    }

    /**
     * Get createdAt
     *
     * @return DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @ORM\PrePersist
     */
    public function setCreatedAtValue()
    {
        if (!$this->createdAt) {
            $this->createdAt = new DateTime();
        }
    }


    /**
     * Set createdAt
     *
     * @param DateTime $createdAt
     * @return NotificationsModels
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get createdBy
     *
     * @return Users
     */
    public function getCreatedBy()
    {
        return $this->createdBy;
    }

    /**
     * Set createdBy
     *
     * @param Users $createdBy
     * @return NotificationsModels
     */
    public function setCreatedBy($createdBy)
    {
        $this->createdBy = $createdBy;

        return $this;
    }


    /**
     * Get isRemoved
     *
     * @return boolean
     */
    public function getIsRemoved()
    {
        return $this->isRemoved;
    }

    /**
     * Set isRemoved
     *
     * @param boolean $isRemoved
     * @return NotificationsModels
     */
    public function setIsRemoved($isRemoved)
    {
        $this->isRemoved = $isRemoved;

        return $this;
    }

    /**
     * Get removedBy
     *
     * @return Users
     */
    public function getRemovedBy()
    {
        return $this->removedBy;
    }

    /**
     * Set removedBy
     *
     * @param Users $removedBy
     * @return NotificationsModels
     */
    public function setRemovedBy($removedBy)
    {
        $this->removedBy = $removedBy;

        return $this;
    }

    /**
     * Get removedAt
     *
     * @return DateTime
     */
    public function getRemovedAt()
    {
        return $this->removedAt;
    }

    /**
     * Set removedAt
     *
     * @return NotificationsModels
     */
    public function setRemovedAt()
    {
        $this->removedAt = new DateTime();

        return $this;
    }

    /**
     * @return string
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * @param string $subject
     * @return NotificationsModels
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;

        return $this;
    }

}
