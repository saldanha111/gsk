<?php

namespace Nononsense\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Security\Core\User\AdvancedUserInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * User
 * 
 * @ORM\Table(name="users")
 * @ORM\Entity(repositoryClass="Nononsense\UserBundle\Entity\UsersRepository")
 * @ORM\HasLifecycleCallbacks
 * @UniqueEntity("email")
 * @UniqueEntity("username")
 */
class Users implements AdvancedUserInterface, \Serializable
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
     * @ORM\Column(name="username", type="string", length=90, unique=true)
     * @Assert\NotBlank()
     * @Assert\Length(min = "3")
     */
    protected $username;
    
    /**
     * @var string
     *
     * @ORM\Column(name="password", type="string", length=90, nullable=true)
     * @Assert\Length(min = "6")
     */
    protected $password;
    
    /**
     * @var string
     *
     * @ORM\Column(name="salt", type="string", length=90)
     */
    protected $salt;
    
    /**
     * @var string
     *
     * @ORM\Column(name="recoverPass", type="string", length=90, nullable=true)
     */
    private $recoverPass;
    
    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=90, unique=true)
     * @Assert\NotBlank()
     * @Assert\Email()
     */
    protected $email;
    
    /**
     * @ORM\Column(name="phone", type="text", nullable=true)
     */
    protected $phone;
    
    /**
     * @ORM\Column(name="position", type="text", nullable=true)
     */
    protected $position;
    
    /**
     * @var boolean
     *
     * @ORM\Column(name="is_active", type="boolean", nullable=true)
     */
    protected $isActive;

    /**
     * @var boolean
     *
     * @ORM\Column(name="borrado", type="boolean",nullable=true)
     */
    protected $deleted = 0;

    /**
     * @ORM\Column(name="name", type="string", length=90)
     * @Assert\NotBlank()
     */
    protected $name;

    /**
     * @ORM\Column(name="seccion", type="string", length=100, nullable=true)
     */
    protected $seccion;

    /**
     * @var string
     * @ORM\Column(name="area", type="string", length=100,nullable=true)
     */
    protected $area;
    
    /**
     * @var string
     * @ORM\Column(name="description", type="text")
     */
    protected $description;
    
    /**
     * @var string
     *
     * @ORM\Column(name="photo", type="text", nullable=true)
     */
    private $photo;
    
    /**
     * @ORM\ManyToMany(targetEntity="\Nononsense\UserBundle\Entity\Roles", inversedBy="users")
     */
    protected $roles; 
    
    /**
     * @ORM\OneToMany(targetEntity="\Nononsense\GroupBundle\Entity\GroupUsers", mappedBy="user")
     */
    protected $groups;  
    
    /**
     * @ORM\Column(type="date")
     */
    protected $created;
    
    /**
     * @ORM\Column(type="date")
     */
    protected $modified;
    
    /**
     * @ORM\OneToMany(targetEntity="\Nononsense\NotificationsBundle\Entity\Notifications", mappedBy="author")
     */
    protected $notifications;
    
    /**
     * @ORM\OneToMany(targetEntity="\Nononsense\NotificationsBundle\Entity\MessagesUsers", mappedBy="user")
     */
    protected $messages;
    
    /**
     * @ORM\OneToMany(targetEntity="\Nononsense\NewsBundle\Entity\News", mappedBy="user")
     */
    protected $news;

    /**
     * @ORM\OneToMany(targetEntity="\Nononsense\HomeBundle\Entity\Documents", mappedBy="userCreatedEntiy")
     */
    protected $documents;

    /**
     * @ORM\OneToMany(targetEntity="\Nononsense\HomeBundle\Entity\Contracts", mappedBy="userCreatedEntiy")
     */
    protected $contracts;

    /**
     * @ORM\OneToMany(targetEntity="\Nononsense\HomeBundle\Entity\RecordsDocuments", mappedBy="userCreatedEntiy")
     */
    protected $recordsDocuments;

    /**
     * @ORM\OneToMany(targetEntity="\Nononsense\HomeBundle\Entity\RecordsContracts", mappedBy="userCreatedEntiy")
     */
    protected $recordsContracts;

    /**
     * @ORM\OneToMany(targetEntity="\Nononsense\HomeBundle\Entity\DocumentsSignatures", mappedBy="userEntiy")
     */
    protected $documentsSignatures;

    /**
     * @ORM\OneToMany(targetEntity="\Nononsense\HomeBundle\Entity\ContractsSignatures", mappedBy="userEntiy")
     */
    protected $contractsSignatures;

    /**
     * @ORM\OneToMany(targetEntity="\Nononsense\HomeBundle\Entity\RecordsSignatures", mappedBy="userEntiy")
     */
    protected $recordsSignatures;

    /**
     * @ORM\OneToMany(targetEntity="\Nononsense\HomeBundle\Entity\RecordsContractsSignatures", mappedBy="userEntiy")
     */
    protected $recordsContractsSignatures;

    /**
     * @ORM\OneToMany(targetEntity="\Nononsense\HomeBundle\Entity\InstanciasWorkflows", mappedBy="userCreatedEntiy")
     */
    protected $InstanciasWorkflowCreated;

    /**
     * @ORM\OneToMany(targetEntity="\Nononsense\HomeBundle\Entity\EvidenciasStep", mappedBy="userEntiy")
     */
    protected $Evidencias;

    /**
     * @ORM\OneToMany(targetEntity="\Nononsense\HomeBundle\Entity\FirmasStep", mappedBy="userEntiy")
     */
    protected $Firmas;

    /**
     * @ORM\OneToMany(targetEntity="\Nononsense\HomeBundle\Entity\ActivityUser", mappedBy="userEntiy")
     */
    protected $Activity;


    /**
     * @ORM\OneToMany(targetEntity="\Nononsense\HomeBundle\Entity\Revision", mappedBy="userRevisionEntiy")
     */
    protected $Revisions;

    /**
     * @ORM\OneToMany(targetEntity="\Nononsense\HomeBundle\Entity\CancelacionStep", mappedBy="userRevisionEntiy")
     */
    protected $Cancelaciones;

    /**
     * @ORM\OneToMany(targetEntity="\Nononsense\UserBundle\Entity\UsersSection", mappedBy="user")
     */
    protected $sections;

    /**
     * @ORM\OneToMany(targetEntity="\Nononsense\HomeBundle\Entity\ReconciliacionRegistro", mappedBy="userEntiy")
     */
    protected $Reconciliaciones;

    /**
     * @ORM\OneToMany(targetEntity="\Nononsense\HomeBundle\Entity\ReconciliacionRegistro", mappedBy="userValidationEntiy")
     */
    protected $ReconciliacionesValidations;

    /**
     * @ORM\OneToMany(targetEntity="\Nononsense\HomeBundle\Entity\Tokens", mappedBy="user")
     */
    protected $tokens;

    /**
     * @ORM\OneToMany(targetEntity="\Nononsense\HomeBundle\Entity\MaterialCleanCleans", mappedBy="cleanUser")
     */
    protected $materialClean;

    /**
     * @ORM\OneToMany(targetEntity="\Nononsense\HomeBundle\Entity\MaterialCleanCleans", mappedBy="verificationUser")
     */
    protected $materialVerification;

    /**
     * @ORM\OneToMany(targetEntity="\Nononsense\HomeBundle\Entity\MaterialCleanCleans", mappedBy="dirtyMaterialUser")
     */
    protected $materialDirty;

    /**
     * @ORM\OneToMany(targetEntity="\Nononsense\HomeBundle\Entity\MaterialCleanCleans", mappedBy="reviewUser")
     */
    protected $materialReview;


    /**
     * Users constructor.
     */
    
    public function __construct()
    {
        $this->templates = new ArrayCollection();
        $this->notifications = new ArrayCollection();
        $this->messages = new ArrayCollection();
        $this->roles = new ArrayCollection();
        $this->groups = new ArrayCollection();

    }
    
    /**
     * To string magic method
     *
     * @return string
     */
    public function __toString() {
        return $this->getName();
    }
    
    public function isAccountNonExpired()
    {
        return true;
    }

    public function isAccountNonLocked()
    {
        return true;
    }

    public function isCredentialsNonExpired()
    {
        return true;
    }

    public function isEnabled()
    {
        return $this->isActive;
    }

    /**
     * Erase credentials
     */
    public function eraseCredentials() {
        
    }

    /**
     * Get password
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Get salt
     *
     * @return string
     */
    public function getSalt()
    {
        return $this->salt;
    }

    /**
     * Get username
     *
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
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
     * Add role
     *
     * @param \Nononsense\UserBundle\Entity\Roles $role
     * @return User
     */
    public function addRole(\Nononsense\UserBundle\Entity\Roles $role)
    {
        $this->roles[] = $role;

        return $this;
    }

    /**
     * Remove roles
     *
     * @param \Nononsense\UserBundle\Entity\Roles $roles
     */
    public function removeRole(\Nononsense\UserBundle\Entity\Roles $roles)
    {
        $this->roles->removeElement($roles);
    }

    /**
     * Get roles
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getRoles()
    {
        return $this->roles->toArray();
    } 
    

    public function serialize() {
        return \json_encode(array(
            $this->id,
            $this->email,
            $this->salt,
            $this->password,
            $this->isActive,
            $this->recoverPass,
            $this->username,
            $this->created,
            $this->modified,
        ));    
    }

    public function unserialize($serialized) {
        list (
            $this->id,
            $this->email,
            $this->salt,
            $this->password,
            $this->isActive,
            $this->recoverPass,
            $this->username,
            $this->created,
            $this->modified,
        ) = \json_decode($serialized);
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
     * Set username
     *
     * @param string $username
     * @return Users
     */
    public function setUsername($username)
    {
        $this->username = $username;

        return $this;
    }

    /**
     * Set password
     *
     * @param string $password
     * @return Users
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Set salt
     *
     * @param string $salt
     * @return Users
     */
    public function setSalt($salt)
    {
        $this->salt = $salt;

        return $this;
    }

    /**
     * Set email
     *
     * @param string $email
     * @return Users
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     *
     * @return string 
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set isActive
     *
     * @param boolean $isActive
     * @return Users
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
     * Set name
     *
     * @param string $name
     * @return Users
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
     * Set description
     *
     * @param string $description
     * @return Users
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string 
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     * @return Users
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
     * @return Users
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
     * Add groups
     *
     * @param \Nononsense\GroupBundle\Entity\Groups $groups
     * @return Users
     */
    public function addGroup(\Nononsense\GroupBundle\Entity\Groups $groups)
    {
        $this->groups[] = $groups;

        return $this;
    }

    /**
     * Remove groups
     *
     * @param \Nononsense\GroupBundle\Entity\Groups $groups
     */
    public function removeGroup(\Nononsense\GroupBundle\Entity\Groups $groups)
    {
        $this->groups->removeElement($groups);
    }

    /**
     * Get groups
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getGroups()
    {
        return $this->groups;
    }

    /**
     * Add users
     *
     * @param \Nononsense\UserBundle\Entity\Users $users
     * @return Users
     */
    public function addUser(\Nononsense\UserBundle\Entity\Users $users)
    {
        $this->users[] = $users;

        return $this;
    }

    /**
     * Remove users
     *
     * @param \Nononsense\UserBundle\Entity\Users $users
     */
    public function removeUser(\Nononsense\UserBundle\Entity\Users $users)
    {
        $this->users->removeElement($users);
    }


    /**
     * Add notifications
     *
     * @param \Nononsense\NotificationsBundle\Entity\Notifications $notifications
     * @return Users
     */
    public function addNotification(\Nononsense\NotificationsBundle\Entity\Notifications $notifications)
    {
        $this->notifications[] = $notifications;

        return $this;
    }

    /**
     * Remove notifications
     *
     * @param \Nononsense\NotificationsBundle\Entity\Notifications $notifications
     */
    public function removeNotification(\Nononsense\NotificationsBundle\Entity\Notifications $notifications)
    {
        $this->notifications->removeElement($notifications);
    }

    /**
     * Get notifications
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getNotifications()
    {
        return $this->notifications;
    }

    /**
     * Set recoverPass
     *
     * @param string $recoverPass
     * @return Users
     */
    public function setRecoverPass($recoverPass)
    {
        $this->recoverPass = $recoverPass;

        return $this;
    }

    /**
     * Get recoverPass
     *
     * @return string 
     */
    public function getRecoverPass()
    {
        return $this->recoverPass;
    }

    /**
     * Set photo
     *
     * @param string $photo
     * @return Users
     */
    public function setPhoto($photo)
    {
        $this->photo = $photo;

        return $this;
    }

    /**
     * Get photo
     *
     * @return string 
     */
    public function getPhoto()
    {
        return $this->photo;
    }

    /**
     * Set phone
     *
     * @param string $phone
     * @return Users
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;

        return $this;
    }

    /**
     * Get phone
     *
     * @return string 
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * Set position
     *
     * @param string $position
     * @return Users
     */
    public function setPosition($position)
    {
        $this->position = $position;

        return $this;
    }

    /**
     * Get position
     *
     * @return string 
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * Add messages
     *
     * @param \Nononsense\NotificationsBundle\Entity\MessagesUsers $messages
     * @return Users
     */
    public function addMessage(\Nononsense\NotificationsBundle\Entity\MessagesUsers $messages)
    {
        $this->messages[] = $messages;

        return $this;
    }

    /**
     * Remove messages
     *
     * @param \Nononsense\NotificationsBundle\Entity\MessagesUsers $messages
     */
    public function removeMessage(\Nononsense\NotificationsBundle\Entity\MessagesUsers $messages)
    {
        $this->messages->removeElement($messages);
    }

    /**
     * Get messages
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getMessages()
    {
        return $this->messages;
    }

    /**
     * Add news
     *
     * @param \Nononsense\NewsBundle\Entity\News $news
     * @return Users
     */
    public function addNews(\Nononsense\NewsBundle\Entity\News $news)
    {
        $this->news[] = $news;

        return $this;
    }

    /**
     * Remove news
     *
     * @param \Nononsense\NewsBundle\Entity\News $news
     */
    public function removeNews(\Nononsense\NewsBundle\Entity\News $news)
    {
        $this->news->removeElement($news);
    }

    /**
     * Get news
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getNews()
    {
        return $this->news;
    }

    /**
     * Add instanciasworkflowscolaborated
     *
     * @param \Nononsense\UserBundle\Entity\Roles $instanciasworkflowscolaborated
     * @return Users
     */
    public function addInstanciasworkflowscolaborated(\Nononsense\UserBundle\Entity\Roles $instanciasworkflowscolaborated)
    {
        $this->instanciasworkflowscolaborated[] = $instanciasworkflowscolaborated;

        return $this;
    }

    /**
     * Remove instanciasworkflowscolaborated
     *
     * @param \Nononsense\UserBundle\Entity\Roles $instanciasworkflowscolaborated
     */
    public function removeInstanciasworkflowscolaborated(\Nononsense\UserBundle\Entity\Roles $instanciasworkflowscolaborated)
    {
        $this->instanciasworkflowscolaborated->removeElement($instanciasworkflowscolaborated);
    }

    /**
     * Get instanciasworkflowscolaborated
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getInstanciasworkflowscolaborated()
    {
        return $this->instanciasworkflowscolaborated;
    }

    /**
     * Set seccion
     *
     * @param string $seccion
     * @return Users
     */
    public function setSeccion($seccion)
    {
        $this->seccion = $seccion;

        return $this;
    }

    /**
     * Get seccion
     *
     * @return string 
     */
    public function getSeccion()
    {
        return $this->seccion;
    }

    /**
     * Set area
     *
     * @param string $area
     * @return Users
     */
    public function setArea($area)
    {
        $this->area = $area;

        return $this;
    }

    /**
     * Get area
     *
     * @return string 
     */
    public function getArea()
    {
        return $this->area;
    }

    /**
     * Add InstanciasWorkflowCreated
     *
     * @param \Nononsense\HomeBundle\Entity\InstanciasWorkflows $instanciasWorkflowCreated
     * @return Users
     */
    public function addInstanciasWorkflowCreated(\Nononsense\HomeBundle\Entity\InstanciasWorkflows $instanciasWorkflowCreated)
    {
        $this->InstanciasWorkflowCreated[] = $instanciasWorkflowCreated;

        return $this;
    }

    /**
     * Remove InstanciasWorkflowCreated
     *
     * @param \Nononsense\HomeBundle\Entity\InstanciasWorkflows $instanciasWorkflowCreated
     */
    public function removeInstanciasWorkflowCreated(\Nononsense\HomeBundle\Entity\InstanciasWorkflows $instanciasWorkflowCreated)
    {
        $this->InstanciasWorkflowCreated->removeElement($instanciasWorkflowCreated);
    }

    /**
     * Get InstanciasWorkflowCreated
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getInstanciasWorkflowCreated()
    {
        return $this->InstanciasWorkflowCreated;
    }

    /**
     * Add InstanciasWorkflowValidatedN1
     *
     * @param \Nononsense\HomeBundle\Entity\InstanciasWorkflows $instanciasWorkflowValidatedN1
     * @return Users
     */
    public function addInstanciasWorkflowValidatedN1(\Nononsense\HomeBundle\Entity\InstanciasWorkflows $instanciasWorkflowValidatedN1)
    {
        $this->InstanciasWorkflowValidatedN1[] = $instanciasWorkflowValidatedN1;

        return $this;
    }

    /**
     * Remove InstanciasWorkflowValidatedN1
     *
     * @param \Nononsense\HomeBundle\Entity\InstanciasWorkflows $instanciasWorkflowValidatedN1
     */
    public function removeInstanciasWorkflowValidatedN1(\Nononsense\HomeBundle\Entity\InstanciasWorkflows $instanciasWorkflowValidatedN1)
    {
        $this->InstanciasWorkflowValidatedN1->removeElement($instanciasWorkflowValidatedN1);
    }

    /**
     * Get InstanciasWorkflowValidatedN1
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getInstanciasWorkflowValidatedN1()
    {
        return $this->InstanciasWorkflowValidatedN1;
    }

    /**
     * Add InstanciasWorkflowValidatedN2
     *
     * @param \Nononsense\HomeBundle\Entity\InstanciasWorkflows $instanciasWorkflowValidatedN2
     * @return Users
     */
    public function addInstanciasWorkflowValidatedN2(\Nononsense\HomeBundle\Entity\InstanciasWorkflows $instanciasWorkflowValidatedN2)
    {
        $this->InstanciasWorkflowValidatedN2[] = $instanciasWorkflowValidatedN2;

        return $this;
    }

    /**
     * Remove InstanciasWorkflowValidatedN2
     *
     * @param \Nononsense\HomeBundle\Entity\InstanciasWorkflows $instanciasWorkflowValidatedN2
     */
    public function removeInstanciasWorkflowValidatedN2(\Nononsense\HomeBundle\Entity\InstanciasWorkflows $instanciasWorkflowValidatedN2)
    {
        $this->InstanciasWorkflowValidatedN2->removeElement($instanciasWorkflowValidatedN2);
    }

    /**
     * Get InstanciasWorkflowValidatedN2
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getInstanciasWorkflowValidatedN2()
    {
        return $this->InstanciasWorkflowValidatedN2;
    }

    /**
     * Add Revisions
     *
     * @param \Nononsense\HomeBundle\Entity\Revision $revisions
     * @return Users
     */
    public function addRevision(\Nononsense\HomeBundle\Entity\Revision $revisions)
    {
        $this->Revisions[] = $revisions;

        return $this;
    }

    /**
     * Remove Revisions
     *
     * @param \Nononsense\HomeBundle\Entity\Revision $revisions
     */
    public function removeRevision(\Nononsense\HomeBundle\Entity\Revision $revisions)
    {
        $this->Revisions->removeElement($revisions);
    }

    /**
     * Get Revisions
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getRevisions()
    {
        return $this->Revisions;
    }

    /**
     * Add sections
     *
     * @param \Nononsense\UserBundle\Entity\Userssection $sections
     * @return Users
     */
    public function addSection(\Nononsense\UserBundle\Entity\UsersSection $sections)
    {
        $this->sections[] = $sections;

        return $this;
    }

    /**
     * Remove sections
     *
     * @param \Nononsense\UserBundle\Entity\Userssection $sections
     */
    public function removeSection(\Nononsense\UserBundle\Entity\UsersSection $sections)
    {
        $this->sections->removeElement($sections);
    }

    /**
     * Get sections
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getSections()
    {
        return $this->sections;
    }


    /**
     * Add InstanciasWorkflowValidatedLogistica1
     *
     * @param \Nononsense\HomeBundle\Entity\InstanciasWorkflows $instanciasWorkflowValidatedLogistica1
     * @return Users
     */
    public function addInstanciasWorkflowValidatedLogistica1(\Nononsense\HomeBundle\Entity\InstanciasWorkflows $instanciasWorkflowValidatedLogistica1)
    {
        $this->InstanciasWorkflowValidatedLogistica1[] = $instanciasWorkflowValidatedLogistica1;

        return $this;
    }

    /**
     * Remove InstanciasWorkflowValidatedLogistica1
     *
     * @param \Nononsense\HomeBundle\Entity\InstanciasWorkflows $instanciasWorkflowValidatedLogistica1
     */
    public function removeInstanciasWorkflowValidatedLogistica1(\Nononsense\HomeBundle\Entity\InstanciasWorkflows $instanciasWorkflowValidatedLogistica1)
    {
        $this->InstanciasWorkflowValidatedLogistica1->removeElement($instanciasWorkflowValidatedLogistica1);
    }

    /**
     * Get InstanciasWorkflowValidatedLogistica1
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getInstanciasWorkflowValidatedLogistica1()
    {
        return $this->InstanciasWorkflowValidatedLogistica1;
    }

    /**
     * Add InstanciasWorkflowColaboratedLogistica
     *
     * @param \Nononsense\HomeBundle\Entity\InstanciasWorkflows $instanciasWorkflowColaboratedLogistica
     * @return Users
     */
    public function addInstanciasWorkflowColaboratedLogistica(\Nononsense\HomeBundle\Entity\InstanciasWorkflows $instanciasWorkflowColaboratedLogistica)
    {
        $this->InstanciasWorkflowColaboratedLogistica[] = $instanciasWorkflowColaboratedLogistica;

        return $this;
    }

    /**
     * Remove InstanciasWorkflowColaboratedLogistica
     *
     * @param \Nononsense\HomeBundle\Entity\InstanciasWorkflows $instanciasWorkflowColaboratedLogistica
     */
    public function removeInstanciasWorkflowColaboratedLogistica(\Nononsense\HomeBundle\Entity\InstanciasWorkflows $instanciasWorkflowColaboratedLogistica)
    {
        $this->InstanciasWorkflowColaboratedLogistica->removeElement($instanciasWorkflowColaboratedLogistica);
    }

    /**
     * Get InstanciasWorkflowColaboratedLogistica
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getInstanciasWorkflowColaboratedLogistica()
    {
        return $this->InstanciasWorkflowColaboratedLogistica;
    }

    /**
     * Add InstanciasWorkflowValidatedLogistica
     *
     * @param \Nononsense\HomeBundle\Entity\InstanciasWorkflows $instanciasWorkflowValidatedLogistica
     * @return Users
     */
    public function addInstanciasWorkflowValidatedLogistica(\Nononsense\HomeBundle\Entity\InstanciasWorkflows $instanciasWorkflowValidatedLogistica)
    {
        $this->InstanciasWorkflowValidatedLogistica[] = $instanciasWorkflowValidatedLogistica;

        return $this;
    }

    /**
     * Remove InstanciasWorkflowValidatedLogistica
     *
     * @param \Nononsense\HomeBundle\Entity\InstanciasWorkflows $instanciasWorkflowValidatedLogistica
     */
    public function removeInstanciasWorkflowValidatedLogistica(\Nononsense\HomeBundle\Entity\InstanciasWorkflows $instanciasWorkflowValidatedLogistica)
    {
        $this->InstanciasWorkflowValidatedLogistica->removeElement($instanciasWorkflowValidatedLogistica);
    }

    /**
     * Get InstanciasWorkflowValidatedLogistica
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getInstanciasWorkflowValidatedLogistica()
    {
        return $this->InstanciasWorkflowValidatedLogistica;
    }



    /**
     * Set deleted
     *
     * @param boolean $deleted
     * @return Users
     */
    public function setDeleted($deleted)
    {
        $this->deleted = $deleted;

        return $this;
    }

    /**
     * Get deleted
     *
     * @return boolean 
     */
    public function getDeleted()
    {
        return $this->deleted;
    }

    /**
     * Add Cancelaciones
     *
     * @param \Nononsense\HomeBundle\Entity\CancelacionStep $cancelaciones
     * @return Users
     */
    public function addCancelacione(\Nononsense\HomeBundle\Entity\CancelacionStep $cancelaciones)
    {
        $this->Cancelaciones[] = $cancelaciones;

        return $this;
    }

    /**
     * Remove Cancelaciones
     *
     * @param \Nononsense\HomeBundle\Entity\CancelacionStep $cancelaciones
     */
    public function removeCancelacione(\Nononsense\HomeBundle\Entity\CancelacionStep $cancelaciones)
    {
        $this->Cancelaciones->removeElement($cancelaciones);
    }

    /**
     * Get Cancelaciones
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getCancelaciones()
    {
        return $this->Cancelaciones;
    }

    /**
     * Add Evidencias
     *
     * @param \Nononsense\HomeBundle\Entity\EvidenciasStep $evidencias
     * @return Users
     */
    public function addEvidencia(\Nononsense\HomeBundle\Entity\EvidenciasStep $evidencias)
    {
        $this->Evidencias[] = $evidencias;

        return $this;
    }

    /**
     * Remove Evidencias
     *
     * @param \Nononsense\HomeBundle\Entity\EvidenciasStep $evidencias
     */
    public function removeEvidencia(\Nononsense\HomeBundle\Entity\EvidenciasStep $evidencias)
    {
        $this->Evidencias->removeElement($evidencias);
    }

    /**
     * Get Evidencias
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getEvidencias()
    {
        return $this->Evidencias;
    }

    /**
     * Add Firmas
     *
     * @param \Nononsense\HomeBundle\Entity\FirmasStep $firmas
     * @return Users
     */
    public function addFirma(\Nononsense\HomeBundle\Entity\FirmasStep $firmas)
    {
        $this->Firmas[] = $firmas;

        return $this;
    }

    /**
     * Remove Firmas
     *
     * @param \Nononsense\HomeBundle\Entity\FirmasStep $firmas
     */
    public function removeFirma(\Nononsense\HomeBundle\Entity\FirmasStep $firmas)
    {
        $this->Firmas->removeElement($firmas);
    }

    /**
     * Get Firmas
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getFirmas()
    {
        return $this->Firmas;
    }

    /**
     * Add documents
     *
     * @param \Nononsense\HomeBundle\Entity\Documents $documents
     * @return Users
     */
    public function addDocument(\Nononsense\HomeBundle\Entity\Documents $documents)
    {
        $this->documents[] = $documents;

        return $this;
    }

    /**
     * Remove documents
     *
     * @param \Nononsense\HomeBundle\Entity\Documents $documents
     */
    public function removeDocument(\Nononsense\HomeBundle\Entity\Documents $documents)
    {
        $this->documents->removeElement($documents);
    }

    /**
     * Get documents
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getDocuments()
    {
        return $this->documents;
    }

    /**
     * Add recordsDocuments
     *
     * @param \Nononsense\HomeBundle\Entity\RecordsDocuments $recordsDocuments
     * @return Users
     */
    public function addRecordsDocument(\Nononsense\HomeBundle\Entity\RecordsDocuments $recordsDocuments)
    {
        $this->recordsDocuments[] = $recordsDocuments;

        return $this;
    }

    /**
     * Remove recordsDocuments
     *
     * @param \Nononsense\HomeBundle\Entity\RecordsDocuments $recordsDocuments
     */
    public function removeRecordsDocument(\Nononsense\HomeBundle\Entity\RecordsDocuments $recordsDocuments)
    {
        $this->recordsDocuments->removeElement($recordsDocuments);
    }

    /**
     * Get recordsDocuments
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getRecordsDocuments()
    {
        return $this->recordsDocuments;
    }

    /**
     * Add documentsSignatures
     *
     * @param \Nononsense\HomeBundle\Entity\DocumentsSignatures $documentsSignatures
     * @return Users
     */
    public function addDocumentsSignature(\Nononsense\HomeBundle\Entity\DocumentsSignatures $documentsSignatures)
    {
        $this->documentsSignatures[] = $documentsSignatures;

        return $this;
    }

    /**
     * Remove documentsSignatures
     *
     * @param \Nononsense\HomeBundle\Entity\DocumentsSignatures $documentsSignatures
     */
    public function removeDocumentsSignature(\Nononsense\HomeBundle\Entity\DocumentsSignatures $documentsSignatures)
    {
        $this->documentsSignatures->removeElement($documentsSignatures);
    }

    /**
     * Get documentsSignatures
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getDocumentsSignatures()
    {
        return $this->documentsSignatures;
    }

    /**
     * Add recordsSignatures
     *
     * @param \Nononsense\HomeBundle\Entity\RecordsSignatures $recordsSignatures
     * @return Users
     */
    public function addRecordsSignature(\Nononsense\HomeBundle\Entity\RecordsSignatures $recordsSignatures)
    {
        $this->recordsSignatures[] = $recordsSignatures;

        return $this;
    }

    /**
     * Remove recordsSignatures
     *
     * @param \Nononsense\HomeBundle\Entity\RecordsSignatures $recordsSignatures
     */
    public function removeRecordsSignature(\Nononsense\HomeBundle\Entity\RecordsSignatures $recordsSignatures)
    {
        $this->recordsSignatures->removeElement($recordsSignatures);
    }

    /**
     * Get recordsSignatures
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getRecordsSignatures()
    {
        return $this->recordsSignatures;
    }

    /**
     * Add Activity
     *
     * @param \Nononsense\HomeBundle\Entity\ActivityUser $activity
     * @return Users
     */
    public function addActivity(\Nononsense\HomeBundle\Entity\ActivityUser $activity)
    {
        $this->Activity[] = $activity;

        return $this;
    }

    /**
     * Remove Activity
     *
     * @param \Nononsense\HomeBundle\Entity\ActivityUser $activity
     */
    public function removeActivity(\Nononsense\HomeBundle\Entity\ActivityUser $activity)
    {
        $this->Activity->removeElement($activity);
    }

    /**
     * Get Activity
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getActivity()
    {
        return $this->Activity;
    }

    /**
     * Add Reconciliaciones
     *
     * @param \Nononsense\HomeBundle\Entity\ReconciliacionRegistro $reconciliaciones
     * @return Users
     */
    public function addReconciliacione(\Nononsense\HomeBundle\Entity\ReconciliacionRegistro $reconciliaciones)
    {
        $this->Reconciliaciones[] = $reconciliaciones;

        return $this;
    }

    /**
     * Remove Reconciliaciones
     *
     * @param \Nononsense\HomeBundle\Entity\ReconciliacionRegistro $reconciliaciones
     */
    public function removeReconciliacione(\Nononsense\HomeBundle\Entity\ReconciliacionRegistro $reconciliaciones)
    {
        $this->Reconciliaciones->removeElement($reconciliaciones);
    }

    /**
     * Get Reconciliaciones
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getReconciliaciones()
    {
        return $this->Reconciliaciones;
    }

    /**
     * Add ReconciliacionesValidations
     *
     * @param \Nononsense\HomeBundle\Entity\ReconciliacionRegistro $reconciliacionesValidations
     * @return Users
     */
    public function addReconciliacionesValidation(\Nononsense\HomeBundle\Entity\ReconciliacionRegistro $reconciliacionesValidations)
    {
        $this->ReconciliacionesValidations[] = $reconciliacionesValidations;

        return $this;
    }

    /**
     * Remove ReconciliacionesValidations
     *
     * @param \Nononsense\HomeBundle\Entity\ReconciliacionRegistro $reconciliacionesValidations
     */
    public function removeReconciliacionesValidation(\Nononsense\HomeBundle\Entity\ReconciliacionRegistro $reconciliacionesValidations)
    {
        $this->ReconciliacionesValidations->removeElement($reconciliacionesValidations);
    }

    /**
     * Get ReconciliacionesValidations
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getReconciliacionesValidations()
    {
        return $this->ReconciliacionesValidations;
    }

    /**
     * Add contracts
     *
     * @param \Nononsense\HomeBundle\Entity\Contracts $contracts
     * @return Users
     */
    public function addContract(\Nononsense\HomeBundle\Entity\Contracts $contracts)
    {
        $this->contracts[] = $contracts;

        return $this;
    }

    /**
     * Remove contracts
     *
     * @param \Nononsense\HomeBundle\Entity\Contracts $contracts
     */
    public function removeContract(\Nononsense\HomeBundle\Entity\Contracts $contracts)
    {
        $this->contracts->removeElement($contracts);
    }

    /**
     * Get contracts
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getContracts()
    {
        return $this->contracts;
    }

    /**
     * Add recordsContracts
     *
     * @param \Nononsense\HomeBundle\Entity\RecordsContracts $recordsContracts
     * @return Users
     */
    public function addRecordsContract(\Nononsense\HomeBundle\Entity\RecordsContracts $recordsContracts)
    {
        $this->recordsContracts[] = $recordsContracts;

        return $this;
    }

    /**
     * Remove recordsContracts
     *
     * @param \Nononsense\HomeBundle\Entity\RecordsContracts $recordsContracts
     */
    public function removeRecordsContract(\Nononsense\HomeBundle\Entity\RecordsContracts $recordsContracts)
    {
        $this->recordsContracts->removeElement($recordsContracts);
    }

    /**
     * Get recordsContracts
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getRecordsContracts()
    {
        return $this->recordsContracts;
    }

    /**
     * Add contractsSignatures
     *
     * @param \Nononsense\HomeBundle\Entity\ContractsSignatures $contractsSignatures
     * @return Users
     */
    public function addContractsSignature(\Nononsense\HomeBundle\Entity\ContractsSignatures $contractsSignatures)
    {
        $this->contractsSignatures[] = $contractsSignatures;

        return $this;
    }

    /**
     * Remove contractsSignatures
     *
     * @param \Nononsense\HomeBundle\Entity\ContractsSignatures $contractsSignatures
     */
    public function removeContractsSignature(\Nononsense\HomeBundle\Entity\ContractsSignatures $contractsSignatures)
    {
        $this->contractsSignatures->removeElement($contractsSignatures);
    }

    /**
     * Get contractsSignatures
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getContractsSignatures()
    {
        return $this->contractsSignatures;
    }

    /**
     * Add recordsContractsSignatures
     *
     * @param \Nononsense\HomeBundle\Entity\RecordsContractsSignatures $recordsContractsSignatures
     * @return Users
     */
    public function addRecordsContractsSignature(\Nononsense\HomeBundle\Entity\RecordsContractsSignatures $recordsContractsSignatures)
    {
        $this->recordsContractsSignatures[] = $recordsContractsSignatures;

        return $this;
    }

    /**
     * Remove recordsContractsSignatures
     *
     * @param \Nononsense\HomeBundle\Entity\RecordsContractsSignatures $recordsContractsSignatures
     */
    public function removeRecordsContractsSignature(\Nononsense\HomeBundle\Entity\RecordsContractsSignatures $recordsContractsSignatures)
    {
        $this->recordsContractsSignatures->removeElement($recordsContractsSignatures);
    }

    /**
     * Get recordsContractsSignatures
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getRecordsContractsSignatures()
    {
        return $this->recordsContractsSignatures;
    }

    /**
     * Add tokens
     *
     * @param \Nononsense\HomeBundle\Entity\Tokens $tokens
     * @return Users
     */
    public function addToken(\Nononsense\HomeBundle\Entity\Tokens $tokens)
    {
        $this->tokens[] = $tokens;

        return $this;
    }

    /**
     * Remove tokens
     *
     * @param \Nononsense\HomeBundle\Entity\Tokens $tokens
     */
    public function removeToken(\Nononsense\HomeBundle\Entity\Tokens $tokens)
    {
        $this->tokens->removeElement($tokens);
    }

    /**
     * Get tokens
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getTokens()
    {
        return $this->tokens;
    }

    /**
     * Add materialClean
     *
     * @param \Nononsense\HomeBundle\Entity\MaterialCleanCleans $materialClean
     * @return Users
     */
    public function addMaterialClean(\Nononsense\HomeBundle\Entity\MaterialCleanCleans $materialClean)
    {
        $this->materialClean[] = $materialClean;

        return $this;
    }

    /**
     * Remove materialClean
     *
     * @param \Nononsense\HomeBundle\Entity\MaterialCleanCleans $materialClean
     */
    public function removeMaterialClean(\Nononsense\HomeBundle\Entity\MaterialCleanCleans $materialClean)
    {
        $this->materialClean->removeElement($materialClean);
    }

    /**
     * Get materialClean
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getMaterialClean()
    {
        return $this->materialClean;
    }

    /**
     * Add materialVerification
     *
     * @param \Nononsense\HomeBundle\Entity\MaterialCleanCleans $materialVerification
     * @return Users
     */
    public function addMaterialVerification(\Nononsense\HomeBundle\Entity\MaterialCleanCleans $materialVerification)
    {
        $this->materialVerification[] = $materialVerification;

        return $this;
    }

    /**
     * Remove materialVerification
     *
     * @param \Nononsense\HomeBundle\Entity\MaterialCleanCleans $materialVerification
     */
    public function removeMaterialVerification(\Nononsense\HomeBundle\Entity\MaterialCleanCleans $materialVerification)
    {
        $this->materialVerification->removeElement($materialVerification);
    }

    /**
     * Get materialVerification
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getMaterialVerification()
    {
        return $this->materialVerification;
    }

    /**
     * Add materialDirty
     *
     * @param \Nononsense\HomeBundle\Entity\MaterialCleanCleans $materialDirty
     * @return Users
     */
    public function addMaterialDirty(\Nononsense\HomeBundle\Entity\MaterialCleanCleans $materialDirty)
    {
        $this->materialDirty[] = $materialDirty;

        return $this;
    }

    /**
     * Remove materialDirty
     *
     * @param \Nononsense\HomeBundle\Entity\MaterialCleanCleans $materialDirty
     */
    public function removeMaterialDirty(\Nononsense\HomeBundle\Entity\MaterialCleanCleans $materialDirty)
    {
        $this->materialDirty->removeElement($materialDirty);
    }

    /**
     * Get materialDirty
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getMaterialDirty()
    {
        return $this->materialDirty;
    }

    /**
     * Add materialReview
     *
     * @param \Nononsense\HomeBundle\Entity\MaterialCleanCleans $materialReview
     * @return Users
     */
    public function addMaterialReview(\Nononsense\HomeBundle\Entity\MaterialCleanCleans $materialReview)
    {
        $this->materialReview[] = $materialReview;

        return $this;
    }

    /**
     * Remove materialReview
     *
     * @param \Nononsense\HomeBundle\Entity\MaterialCleanCleans $materialReview
     */
    public function removeMaterialReview(\Nononsense\HomeBundle\Entity\MaterialCleanCleans $materialReview)
    {
        $this->materialReview->removeElement($materialReview);
    }

    /**
     * Get materialReview
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getMaterialReview()
    {
        return $this->materialReview;
    }
}
