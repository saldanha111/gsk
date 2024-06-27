<?php

namespace Nononsense\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Security\Core\User\AdvancedUserInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * User
 * 
 * @ORM\Table(name="users")
 * @ORM\Entity(repositoryClass="Nononsense\UserBundle\Entity\UsersRepository")
 * @ORM\HasLifecycleCallbacks
 * @UniqueEntity("email", message="El email ya está en uso.")
 * @UniqueEntity("username", message="El nombre de usuario ya está en uso.")
 * @UniqueEntity("mudId", message="El MUD_ID del usuario ya está en uso.")
 */
class Users implements AdvancedUserInterface, \Serializable
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({"data_new_edition"})
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
     * @Assert\Regex(
     *     pattern     = "/^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9]).{8,}$/",
     *     message     = "La contraseña debe tener 8 caracteres como mínimo y contener al menos uno de los siguientes elementos: mayúsculas, minúsculas y números."
     * )
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
     * @ORM\Column(name="email", type="string", length=90, unique=true, nullable=true)
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
     * @ORM\Column(type="date", nullable=true)
     */
    protected $locked;

    /**
     * @ORM\Column(name="mud_id", type="string", length=20, nullable=true)
     */
    protected $mudId;

    /**
     * @ORM\OneToMany(targetEntity="\Nononsense\GroupBundle\Entity\GroupsSignatures", mappedBy="user")
     */
    protected $groupsSignatures;
    
    /**
     * @ORM\OneToMany(targetEntity="\Nononsense\NotificationsBundle\Entity\Notifications", mappedBy="user")
     */
    protected $notifications;
    
    /**
     * @ORM\OneToMany(targetEntity="\Nononsense\NotificationsBundle\Entity\MessagesUsers", mappedBy="user")
     */
    protected $messages;

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
     * @ORM\OneToMany(targetEntity="\Nononsense\HomeBundle\Entity\TMTests", mappedBy="userEntiy")
     */
    protected $tmTests;

    /**
     * @ORM\OneToMany(targetEntity="\Nononsense\HomeBundle\Entity\TMSignatures", mappedBy="userEntiy")
     */
    protected $tmSignatures;

    /**
     * @ORM\OneToMany(targetEntity="\Nononsense\HomeBundle\Entity\TMTemplates", mappedBy="owner")
     */
    protected $ownerTemplates;

    /**
     * @ORM\OneToMany(targetEntity="\Nononsense\HomeBundle\Entity\TMTemplates", mappedBy="backup")
     */
    protected $backupTeamplates;

    /**
     * @ORM\OneToMany(targetEntity="\Nononsense\HomeBundle\Entity\TMTemplates", mappedBy="applicant")
     */
    protected $applicantTeamplates;

    /**
     * @ORM\OneToMany(targetEntity="\Nononsense\HomeBundle\Entity\TMTemplates", mappedBy="openedBy")
     */
    protected $openedByTeamplates;

    /**
     * @ORM\OneToMany(targetEntity="\Nononsense\HomeBundle\Entity\TMWorkflow", mappedBy="userEntiy")
     */
    protected $tmWorkflows;

    /**
     * @ORM\OneToMany(targetEntity="\Nononsense\HomeBundle\Entity\CVWorkflow", mappedBy="user")
     */
    protected $cvWorkflows;

    /**
     * @ORM\OneToMany(targetEntity="\Nononsense\HomeBundle\Entity\CVSecondWorkflow", mappedBy="user")
     */
    protected $cvSecondWorkflows;

    /**
     * @ORM\OneToMany(targetEntity="\Nononsense\HomeBundle\Entity\CVSignatures", mappedBy="user")
     */
    protected $cvSignatures;

    /**
     * @ORM\OneToMany(targetEntity="\Nononsense\HomeBundle\Entity\CVRecords", mappedBy="user")
     */
    protected $cvRecords;

    /**
     * @ORM\OneToMany(targetEntity="\Nononsense\HomeBundle\Entity\CVRecords", mappedBy="userGxP")
     */
    protected $cvRecordsGxP;

    /**
     * @ORM\OneToMany(targetEntity="\Nononsense\HomeBundle\Entity\CVRecords", mappedBy="openedBy")
     */
    protected $cvRecordsOpen;

    /**
     * @ORM\OneToMany(targetEntity="\Nononsense\HomeBundle\Entity\Areas", mappedBy="fll")
     */
    protected $areas;

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
     * @ORM\OneToMany(targetEntity="\Nononsense\HomeBundle\Entity\MaterialCleanCleans", mappedBy="cancelUser")
     */
    protected $materialCancel;

    /**
     * @ORM\OneToMany(targetEntity="\Nononsense\HomeBundle\Entity\ProductsInputs", mappedBy="user")
     */
    protected $productsInput;

    /**
     * @ORM\OneToMany(targetEntity="\Nononsense\HomeBundle\Entity\ProductsOutputs", mappedBy="user")
     */
    protected $productsOutput;

    /**
     * @ORM\OneToMany(targetEntity="\Nononsense\HomeBundle\Entity\RecordsContractsPinComite", mappedBy="user")
     */
    protected $pinComite;

    /**
     * @ORM\OneToMany(targetEntity="\Nononsense\HomeBundle\Entity\RetentionSignatures", mappedBy="userEntiy")
     */
    protected $retentionSignatures;

    /**
     * @ORM\OneToMany(targetEntity="\Nononsense\HomeBundle\Entity\Delegations", mappedBy="user")
     */
    protected $delegationsusers;

    /**
     * @ORM\OneToMany(targetEntity="\Nononsense\HomeBundle\Entity\Delegations", mappedBy="sustitute")
     */
    protected $delegationssustitutes;

    /**
     * @ORM\OneToMany(targetEntity="\Nononsense\NotificationsBundle\Entity\NotificationsModels", mappedBy="user")
     */
    protected $notificationsModels;

    /**
     * @var boolean
     *
     * @ORM\Column(name="activeDirectory", type="boolean", nullable=true, options={"default": false})
     */
    private $activeDirectory = false;

    /**
     * @ORM\OneToMany(targetEntity="\Nononsense\HomeBundle\Entity\Logs", mappedBy="user")
     */
    private $logs;

    /**
     * @ORM\OneToMany(targetEntity="\Nononsense\HomeBundle\Entity\ArchiveSignatures", mappedBy="userEntiy")
     */
    protected $archiveSignatures;

    /**
     * @ORM\OneToMany(targetEntity="\Nononsense\HomeBundle\Entity\ArchiveRecords", mappedBy="creator")
     */
    protected $archiveRecords;

    /**
     * @var boolean
     *
     * @ORM\Column(name="super_admin", type="boolean", nullable=true)
     */
    protected $superAdmin;


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


    /**
     * Add tmSignatures
     *
     * @param \Nononsense\HomeBundle\Entity\TMSignatures $tmSignatures
     * @return Users
     */
    public function addTmSignature(\Nononsense\HomeBundle\Entity\TMSignatures $tmSignatures)
    {
        $this->tmSignatures[] = $tmSignatures;

        return $this;
    }

    /**
     * Remove tmSignatures
     *
     * @param \Nononsense\HomeBundle\Entity\TMSignatures $tmSignatures
     */
    public function removeTmSignature(\Nononsense\HomeBundle\Entity\TMSignatures $tmSignatures)
    {
        $this->tmSignatures->removeElement($tmSignatures);
    }

    /**
     * Get tmSignatures
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getTmSignatures()
    {
        return $this->tmSignatures;
    }

    /**
     * Add tmWorkflows
     *
     * @param \Nononsense\HomeBundle\Entity\TMWorkflow $tmWorkflows
     * @return Users
     */
    public function addTmWorkflow(\Nononsense\HomeBundle\Entity\TMWorkflow $tmWorkflows)
    {
        $this->tmWorkflows[] = $tmWorkflows;

        return $this;
    }

    /**
     * Remove tmWorkflows
     *
     * @param \Nononsense\HomeBundle\Entity\TMWorkflow $tmWorkflows
     */
    public function removeTmWorkflow(\Nononsense\HomeBundle\Entity\TMWorkflow $tmWorkflows)
    {
        $this->tmWorkflows->removeElement($tmWorkflows);
    }

    /**
     * Get tmWorkflows
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getTmWorkflows()
    {
        return $this->tmWorkflows;
    }

    /**
     * Add productsInput
     *
     * @param \Nononsense\HomeBundle\Entity\ProductsInputs $productsInput
     * @return Users
     */
    public function addProductsInput(\Nononsense\HomeBundle\Entity\ProductsInputs $productsInput)
    {
        $this->productsInput[] = $productsInput;

        return $this;
    }

    /**
     * Remove productsInput
     *
     * @param \Nononsense\HomeBundle\Entity\ProductsInputs $productsInput
     */
    public function removeProductsInput(\Nononsense\HomeBundle\Entity\ProductsInputs $productsInput)
    {
        $this->productsInput->removeElement($productsInput);
    }

    /**
     * Get productsInput
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getProductsInput()
    {
        return $this->productsInput;
    }

    /**
     * Add productsOutput
     *
     * @param \Nononsense\HomeBundle\Entity\ProductsOutputs $productsOutput
     * @return Users
     */
    public function addProductsOutput(\Nononsense\HomeBundle\Entity\ProductsOutputs $productsOutput)
    {
        $this->productsOutput[] = $productsOutput;

        return $this;
    }

    /**
     * Remove productsOutput
     *
     * @param \Nononsense\HomeBundle\Entity\ProductsOutputs $productsOutput
     */
    public function removeProductsOutput(\Nononsense\HomeBundle\Entity\ProductsOutputs $productsOutput)
    {
        $this->productsOutput->removeElement($productsOutput);
    }

    /**
     * Get productsOutput
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getProductsOutput()
    {
        return $this->productsOutput;
    }

    /**
     * Add pinComite
     *
     * @param \Nononsense\HomeBundle\Entity\RecordsContractsPinComite $pinComite
     * @return Users
     */
    public function addPinComite(\Nononsense\HomeBundle\Entity\RecordsContractsPinComite $pinComite)
    {
        $this->pinComite[] = $pinComite;

        return $this;
    }

    /**
     * Remove pinComite
     *
     * @param \Nononsense\HomeBundle\Entity\RecordsContractsPinComite $pinComite
     */
    public function removePinComite(\Nononsense\HomeBundle\Entity\RecordsContractsPinComite $pinComite)
    {
        $this->pinComite->removeElement($pinComite);
    }

    /**
     * Get pinComite
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getPinComite()
    {
        return $this->pinComite;
    }

    /**
     * Add ownerTemplates
     *
     * @param \Nononsense\HomeBundle\Entity\TMTemplates $ownerTemplates
     * @return Users
     */
    public function addOwnerTemplate(\Nononsense\HomeBundle\Entity\TMTemplates $ownerTemplates)
    {
        $this->ownerTemplates[] = $ownerTemplates;

        return $this;
    }

    /**
     * Remove ownerTemplates
     *
     * @param \Nononsense\HomeBundle\Entity\TMTemplates $ownerTemplates
     */
    public function removeOwnerTemplate(\Nononsense\HomeBundle\Entity\TMTemplates $ownerTemplates)
    {
        $this->ownerTemplates->removeElement($ownerTemplates);
    }

    /**
     * Get ownerTemplates
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getOwnerTemplates()
    {
        return $this->ownerTemplates;
    }

    /**
     * Add backupTeamplates
     *
     * @param \Nononsense\HomeBundle\Entity\TMTemplates $backupTeamplates
     * @return Users
     */
    public function addBackupTeamplate(\Nononsense\HomeBundle\Entity\TMTemplates $backupTeamplates)
    {
        $this->backupTeamplates[] = $backupTeamplates;

        return $this;
    }

    /**
     * Remove backupTeamplates
     *
     * @param \Nononsense\HomeBundle\Entity\TMTemplates $backupTeamplates
     */
    public function removeBackupTeamplate(\Nononsense\HomeBundle\Entity\TMTemplates $backupTeamplates)
    {
        $this->backupTeamplates->removeElement($backupTeamplates);
    }

    /**
     * Get backupTeamplates
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getBackupTeamplates()
    {
        return $this->backupTeamplates;
    }

    /**
     * Add applicantTeamplates
     *
     * @param \Nononsense\HomeBundle\Entity\TMTemplates $applicantTeamplates
     * @return Users
     */
    public function addApplicantTeamplate(\Nononsense\HomeBundle\Entity\TMTemplates $applicantTeamplates)
    {
        $this->applicantTeamplates[] = $applicantTeamplates;

        return $this;
    }

    /**
     * Remove applicantTeamplates
     *
     * @param \Nononsense\HomeBundle\Entity\TMTemplates $applicantTeamplates
     */
    public function removeApplicantTeamplate(\Nononsense\HomeBundle\Entity\TMTemplates $applicantTeamplates)
    {
        $this->applicantTeamplates->removeElement($applicantTeamplates);
    }

    /**
     * Get applicantTeamplates
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getApplicantTeamplates()
    {
        return $this->applicantTeamplates;
    }

    /**
     * Add openedByTeamplates
     *
     * @param \Nononsense\HomeBundle\Entity\TMTemplates $openedByTeamplates
     * @return Users
     */
    public function addOpenedByTeamplate(\Nononsense\HomeBundle\Entity\TMTemplates $openedByTeamplates)
    {
        $this->openedByTeamplates[] = $openedByTeamplates;

        return $this;
    }

    /**
     * Remove openedByTeamplates
     *
     * @param \Nononsense\HomeBundle\Entity\TMTemplates $openedByTeamplates
     */
    public function removeOpenedByTeamplate(\Nononsense\HomeBundle\Entity\TMTemplates $openedByTeamplates)
    {
        $this->openedByTeamplates->removeElement($openedByTeamplates);
    }

    /**
     * Get openedByTeamplates
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getOpenedByTeamplates()
    {
        return $this->openedByTeamplates;
    }

    /**
     * Add tmTests
     *
     * @param \Nononsense\HomeBundle\Entity\TMTests $tmTests
     * @return Users
     */
    public function addTmTest(\Nononsense\HomeBundle\Entity\TMTests $tmTests)
    {
        $this->tmTests[] = $tmTests;

        return $this;
    }

    /**
     * Remove tmTests
     *
     * @param \Nononsense\HomeBundle\Entity\TMTests $tmTests
     */
    public function removeTmTest(\Nononsense\HomeBundle\Entity\TMTests $tmTests)
    {
        $this->tmTests->removeElement($tmTests);
    }

    /**
     * Get tmTests
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getTmTests()
    {
        return $this->tmTests;
    }

    /**
     * Set mudId
     *
     * @param string $mudId
     * @return Users
     */
    public function setMudId($mudId)
    {
        $this->mudId = $mudId;

        return $this;
    }

    /**
     * Get mudId
     *
     * @return string 
     */
    public function getMudId()
    {
        return $this->mudId;
    }

    /**
     * Set locked
     *
     * @param \DateTime $locked
     * @return Users
     */
    public function setLocked($locked)
    {
        $this->locked = $locked;

        return $this;
    }

    /**
     * Get locked
     *
     * @return \DateTime 
     */
    public function getLocked()
    {
        return $this->locked;
    }

    /**
     * Add cvWorkflows
     *
     * @param \Nononsense\HomeBundle\Entity\CVWorkflow $cvWorkflows
     * @return Users
     */
    public function addCvWorkflow(\Nononsense\HomeBundle\Entity\CVWorkflow $cvWorkflows)
    {
        $this->cvWorkflows[] = $cvWorkflows;
    }

    /**
     * Set activeDirectory
     *
     * @param boolean $activeDirectory
     * @return AccountRequests
     */
    public function setActiveDirectory($activeDirectory)
    {
        $this->activeDirectory = $activeDirectory;
        return $this;
    }

    /**
     * Remove cvWorkflows
     *
     * @param \Nononsense\HomeBundle\Entity\CVWorkflow $cvWorkflows
     */
    public function removeCvWorkflow(\Nononsense\HomeBundle\Entity\CVWorkflow $cvWorkflows)
    {
        $this->cvWorkflows->removeElement($cvWorkflows);
    }

    /**
     * Get cvWorkflows
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getCvWorkflows()
    {
        return $this->cvWorkflows;
    }

    /**
     * Add cvSignatures
     *
     * @param \Nononsense\HomeBundle\Entity\CVSignatures $cvSignatures
     * @return Users
     */
    public function addCvSignature(\Nononsense\HomeBundle\Entity\CVSignatures $cvSignatures)
    {
        $this->cvSignatures[] = $cvSignatures;
    }

    /**
     * Get activeDirectory
     *
     * @return boolean 
     */
    public function getActiveDirectory()
    {
        return $this->activeDirectory;
    }

    /**
     * Add logs
     *
     * @param \Nononsense\HomeBundle\Entity\Logs $logs
     * @return Users
     */
    public function addLog(\Nononsense\HomeBundle\Entity\Logs $logs)
    {
        $this->logs[] = $logs;
        return $this;
    }

    /**
     * Remove cvSignatures
     *
     * @param \Nononsense\HomeBundle\Entity\CVSignatures $cvSignatures
     */
    public function removeCvSignature(\Nononsense\HomeBundle\Entity\CVSignatures $cvSignatures)
    {
        $this->cvSignatures->removeElement($cvSignatures);
    }

    /**
     * Get cvSignatures
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getCvSignatures()
    {
        return $this->cvSignatures;
    }

    /**
     * Add cvRecords
     *
     * @param \Nononsense\HomeBundle\Entity\CVRecords $cvRecords
     * @return Users
     */
    public function addCvRecord(\Nononsense\HomeBundle\Entity\CVRecords $cvRecords)
    {
        $this->cvRecords[] = $cvRecords;

        return $this;
    }

    /**
     * Remove cvRecords
     *
     * @param \Nononsense\HomeBundle\Entity\CVRecords $cvRecords
     */
    public function removeCvRecord(\Nononsense\HomeBundle\Entity\CVRecords $cvRecords)
    {
        $this->cvRecords->removeElement($cvRecords);
    }

    /**
     * Get cvRecords
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getCvRecords()
    {
        return $this->cvRecords;
    }

    /**
     * Add cvRecordsGxP
     *
     * @param \Nononsense\HomeBundle\Entity\CVRecords $cvRecordsGxP
     * @return Users
     */
    public function addCvRecordsGxP(\Nononsense\HomeBundle\Entity\CVRecords $cvRecordsGxP)
    {
        $this->cvRecordsGxP[] = $cvRecordsGxP;

        return $this;
    }

    /**
     * Remove cvRecordsGxP
     *
     * @param \Nononsense\HomeBundle\Entity\CVRecords $cvRecordsGxP
     */
    public function removeCvRecordsGxP(\Nononsense\HomeBundle\Entity\CVRecords $cvRecordsGxP)
    {
        $this->cvRecordsGxP->removeElement($cvRecordsGxP);
    }

    /**
     * Get cvRecordsGxP
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getCvRecordsGxP()
    {
        return $this->cvRecordsGxP;
    }

    /**
     * Add areas
     *
     * @param \Nononsense\HomeBundle\Entity\Areas $areas
     * @return Users
     */
    public function addArea(\Nononsense\HomeBundle\Entity\Areas $areas)
    {
        $this->areas[] = $areas;

        return $this;
    }

    /**
     * Remove areas
     *
     * @param \Nononsense\HomeBundle\Entity\Areas $areas
     */
    public function removeArea(\Nononsense\HomeBundle\Entity\Areas $areas)
    {
        $this->areas->removeElement($areas);
    }

    /**
     * Get areas
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getAreas()
    {
        return $this->areas;
    }

    /**
     * Add cvSecondWorkflows
     *
     * @param \Nononsense\HomeBundle\Entity\CVSecondWorkflow $cvSecondWorkflows
     * @return Users
     */
    public function addCvSecondWorkflow(\Nononsense\HomeBundle\Entity\CVSecondWorkflow $cvSecondWorkflows)
    {
        $this->cvSecondWorkflows[] = $cvSecondWorkflows;

        return $this;
    }

    /**
     * Remove cvSecondWorkflows
     *
     * @param \Nononsense\HomeBundle\Entity\CVSecondWorkflow $cvSecondWorkflows
     */
    public function removeCvSecondWorkflow(\Nononsense\HomeBundle\Entity\CVSecondWorkflow $cvSecondWorkflows)
    {
        $this->cvSecondWorkflows->removeElement($cvSecondWorkflows);
    }

    /**
     * Get cvSecondWorkflows
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getCvSecondWorkflows()
    {
        return $this->cvSecondWorkflows;
    }

    /**
     * Add cvRecordsOpen
     *
     * @param \Nononsense\HomeBundle\Entity\CVRecords $cvRecordsOpen
     * @return Users
     */
    public function addCvRecordsOpen(\Nononsense\HomeBundle\Entity\CVRecords $cvRecordsOpen)
    {
        $this->cvRecordsOpen[] = $cvRecordsOpen;

        return $this;
    }

    /**
     * Remove cvRecordsOpen
     *
     * @param \Nononsense\HomeBundle\Entity\CVRecords $cvRecordsOpen
     */
    public function removeCvRecordsOpen(\Nononsense\HomeBundle\Entity\CVRecords $cvRecordsOpen)
    {
        $this->cvRecordsOpen->removeElement($cvRecordsOpen);
    }

    /**
     * Get cvRecordsOpen
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getCvRecordsOpen()
    {
        return $this->cvRecordsOpen;
    }

    /**
     * Add delegationsusers
     *
     * @param \Nononsense\HomeBundle\Entity\Delegations $delegationsusers
     * @return Users
     */
    public function addDelegationsuser(\Nononsense\HomeBundle\Entity\Delegations $delegationsusers)
    {
        $this->delegationsusers[] = $delegationsusers;

        return $this;
    }

    /**
     * Remove delegationsusers
     *
     * @param \Nononsense\HomeBundle\Entity\Delegations $delegationsusers
     */
    public function removeDelegationsuser(\Nononsense\HomeBundle\Entity\Delegations $delegationsusers)
    {
        $this->delegationsusers->removeElement($delegationsusers);
    }

    /**
     * Get delegationsusers
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getDelegationsusers()
    {
        return $this->delegationsusers;
    }

    /**
     * Add delegationssustitutes
     *
     * @param \Nononsense\HomeBundle\Entity\Delegations $delegationssustitutes
     * @return Users
     */
    public function addDelegationssustitute(\Nononsense\HomeBundle\Entity\Delegations $delegationssustitutes)
    {
        $this->delegationssustitutes[] = $delegationssustitutes;

        return $this;
    }

    /**
     * Remove delegationssustitutes
     *
     * @param \Nononsense\HomeBundle\Entity\Delegations $delegationssustitutes
     */
    public function removeDelegationssustitute(\Nononsense\HomeBundle\Entity\Delegations $delegationssustitutes)
    {
        $this->delegationssustitutes->removeElement($delegationssustitutes);
    }

    /**
     * Get delegationssustitutes
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getDelegationssustitutes()
    {
        return $this->delegationssustitutes;
    }

    /**
     * Add notificationsModels
     *
     * @param \Nononsense\NotificationsBundle\Entity\NotificationsModels $notificationsModels
     * @return Users
     */
    public function addNotificationsModel(\Nononsense\NotificationsBundle\Entity\NotificationsModels $notificationsModels)
    {
        $this->notificationsModels[] = $notificationsModels;

        return $this;
    }

    /**
     * Remove notificationsModels
     *
     * @param \Nononsense\NotificationsBundle\Entity\NotificationsModels $notificationsModels
     */
    public function removeNotificationsModel(\Nononsense\NotificationsBundle\Entity\NotificationsModels $notificationsModels)
    {
        $this->notificationsModels->removeElement($notificationsModels);
    }

    /**
     * Get notificationsModels
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getNotificationsModels()
    {
        return $this->notificationsModels;
    }

    /**
     * Add retentionSignatures
     *
     * @param \Nononsense\HomeBundle\Entity\RetentionSignatures $retentionSignatures
     * @return Users
     */
    public function addRetentionSignature(\Nononsense\HomeBundle\Entity\RetentionSignatures $retentionSignatures)
    {
        $this->retentionSignatures[] = $retentionSignatures;

        return $this;
    }

    /**
     * Remove retentionSignatures
     *
     * @param \Nononsense\HomeBundle\Entity\RetentionSignatures $retentionSignatures
     */
    public function removeRetentionSignature(\Nononsense\HomeBundle\Entity\RetentionSignatures $retentionSignatures)
    {
        $this->retentionSignatures->removeElement($retentionSignatures);
    }

    /**
     * Get retentionSignatures
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getRetentionSignatures()
    {
        return $this->retentionSignatures;
    }

    /**
     * Add groupsSignatures
     *
     * @param \Nononsense\GroupBundle\Entity\GroupsSignatures $groupsSignatures
     * @return Users
     */
    public function addGroupsSignature(\Nononsense\GroupBundle\Entity\GroupsSignatures $groupsSignatures)
    {
        $this->groupsSignatures[] = $groupsSignatures;

        return $this;
    }

    /**
     * Remove groupsSignatures
     *
     * @param \Nononsense\GroupBundle\Entity\GroupsSignatures $groupsSignatures
     */
    public function removeGroupsSignature(\Nononsense\GroupBundle\Entity\GroupsSignatures $groupsSignatures)
    {
        $this->groupsSignatures->removeElement($groupsSignatures);
    }

    /**
     * Get groupsSignatures
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getGroupsSignatures()
    {
        return $this->groupsSignatures;
    }

    /**
     * Remove logs
     *
     * @param \Nononsense\HomeBundle\Entity\Logs $logs
     */
    public function removeLog(\Nononsense\HomeBundle\Entity\Logs $logs)
    {
        $this->logs->removeElement($logs);
    }

    /**
     * Get logs
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getLogs()
    {
        return $this->logs;
    }

    /**
     * Add materialCancel
     *
     * @param \Nononsense\HomeBundle\Entity\MaterialCleanCleans $materialCancel
     * @return Users
     */
    public function addMaterialCancel(\Nononsense\HomeBundle\Entity\MaterialCleanCleans $materialCancel)
    {
        $this->materialCancel[] = $materialCancel;

        return $this;
    }

    /**
     * Remove materialCancel
     *
     * @param \Nononsense\HomeBundle\Entity\MaterialCleanCleans $materialCancel
     */
    public function removeMaterialCancel(\Nononsense\HomeBundle\Entity\MaterialCleanCleans $materialCancel)
    {
        $this->materialCancel->removeElement($materialCancel);
    }

    /**
     * Get materialCancel
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getMaterialCancel()
    {
        return $this->materialCancel;
    }

    /**
     * Add archiveSignatures
     *
     * @param \Nononsense\HomeBundle\Entity\ArchiveSignatures $archiveSignatures
     * @return Users
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

    /**
     * Add archiveRecords
     *
     * @param \Nononsense\HomeBundle\Entity\ArchiveRecords $archiveRecords
     * @return Users
     */
    public function addArchiveRecord(\Nononsense\HomeBundle\Entity\ArchiveRecords $archiveRecords)
    {
        $this->archiveRecords[] = $archiveRecords;

        return $this;
    }

    /**
     * Remove archiveRecords
     *
     * @param \Nononsense\HomeBundle\Entity\ArchiveRecords $archiveRecords
     */
    public function removeArchiveRecord(\Nononsense\HomeBundle\Entity\ArchiveRecords $archiveRecords)
    {
        $this->archiveRecords->removeElement($archiveRecords);
    }

    /**
     * Get archiveRecords
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getArchiveRecords()
    {
        return $this->archiveRecords;
    }

    /**
     * Set superAdmin
     *
     * @param boolean $superAdmin
     * @return Users
     */
    public function setSuperAdmin($superAdmin)
    {
        $this->superAdmin = $superAdmin;

        return $this;
    }

    /**
     * Get superAdmin
     *
     * @return boolean 
     */
    public function getSuperAdmin()
    {
        return $this->superAdmin;
    }
}
