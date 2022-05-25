<?php

namespace Nononsense\GroupBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * Groups
 *
 * @ORM\Entity
 * @ORM\Table(name="groups")
 * @ORM\Entity(repositoryClass="Nononsense\GroupBundle\Entity\GroupsRepository")
 * @ORM\HasLifecycleCallbacks
 * @UniqueEntity("name")
 * @UniqueEntity("color", message="Please, choose a different color because this one is already used by other group.")
 */
class Groups
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\OneToMany(targetEntity="\Nononsense\GroupBundle\Entity\GroupUsers", mappedBy="group")
     */
    protected $users;
    
    /**
     * @ORM\ManyToMany(targetEntity="\Nononsense\NotificationsBundle\Entity\Notifications", mappedBy="groups")
     */
    protected $notifications;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=90, unique=true)
     */
    protected $name;
    
    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text")
     */
    protected $description;
    
    /**
     * @var string
     *
     * @ORM\Column(name="color", type="string", length=7, unique=true)
     */
    protected $color;

    /**
     * @var string
     *
     * @ORM\Column(name="tipo", type="string", length=10, nullable=true)
     */
    protected $tipo;

    /**
     * @var string
     *
     * @ORM\Column(name="superior", type="integer",  nullable=true)
     */
    protected $superior;
    
    /**
     * @var boolean
     *
     * @ORM\Column(name="is_active", type="boolean", nullable=true)
     */
    protected $isActive;
    
    /**
     * @ORM\Column(type="date")
     */
    protected $created;
    
    /**
     * @ORM\Column(type="date")
     */
    protected $modified;

    /**
     * @ORM\OneToMany(targetEntity="\Nononsense\HomeBundle\Entity\DocumentsSignatures", mappedBy="groupEntiy")
     */
    protected $documentsSignatures;

    /**
     * @ORM\OneToMany(targetEntity="\Nononsense\HomeBundle\Entity\ContractsSignatures", mappedBy="groupEntiy")
     */
    protected $contractsSignatures;

    /**
     * @ORM\OneToMany(targetEntity="\Nononsense\HomeBundle\Entity\RecordsSignatures", mappedBy="groupEntiy")
     */
    protected $recordsSignatures;

    /**
     * @ORM\OneToMany(targetEntity="\Nononsense\HomeBundle\Entity\RecordsContractsSignatures", mappedBy="groupEntiy")
     */
    protected $recordsContractsSignatures;

    /**
     * @ORM\OneToMany(targetEntity="\Nononsense\UserBundle\Entity\GroupsSubsecciones", mappedBy="group")
     */
    protected $groupsSubsecciones;

    /**
     * @ORM\OneToMany(targetEntity="\Nononsense\HomeBundle\Entity\TMWorkflow", mappedBy="groupEntiy")
     */
    protected $tmWorkflows;

    /**
     * @ORM\OneToMany(targetEntity="\Nononsense\HomeBundle\Entity\CVWorkflow", mappedBy="group")
     */
    protected $cvWorkflows;

    /**
     * @ORM\OneToMany(targetEntity="\Nononsense\HomeBundle\Entity\CVSecondWorkflow", mappedBy="group")
     */
    protected $cvSecondWorkflows;

    /**
     * @ORM\OneToMany(targetEntity="\Nononsense\HomeBundle\Entity\AreasGroups", mappedBy="agroup")
     */
    protected $areas;

    /**
     * @ORM\OneToMany(targetEntity="\Nononsense\HomeBundle\Entity\SpecificGroups", mappedBy="group")
     */
    protected $specificGroups;

    /**
     * @ORM\OneToMany(targetEntity="\Nononsense\NotificationsBundle\Entity\NotificationsModels", mappedBy="group")
     */
    protected $notificationsModels;

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
     * Construct
     *
     */
    public function __construct() {
        $this->users = new ArrayCollection();
        $this->notifications = new ArrayCollection();
        $this->codigousuarios = "";
        $this->permisos = "{}";
    }

    /**
     * To string magic method
     *
     * @return string
     */
    public function __toString() {
        return $this->getName();
    }

    /**
     * Set name
     *
     * @param string $name
     * @return Role
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
     * Add users
     *
     * @param \Nononsense\UserBundle\Entity\Users $user
     * @return Groups
     */
    public function addUser(\Nononsense\UserBundle\Entity\Users $user)
    {
        $this->users[] = $user;

        return $this;
    }

    /**
     * Remove users
     *
     * @param \Nononsense\UserBundle\Entity\Users $users
     */
    public function removeUser(\Nononsense\UserBundle\Entity\Users $user)
    {
        $this->user->removeElement($user);
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
     * Get role
     */
    public function getGroup()
    {
        return $this->getName();
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     * @return Groups
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
     * @return Groups
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
     * Get user
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Add notifications
     *
     * @param \Nononsense\NotificationsBundle\Entity\Notifications $notifications
     * @return Groups
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
     * Set description
     *
     * @param string $description
     * @return Groups
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
     * Set color
     *
     * @param string $color
     * @return Groups
     */
    public function setColor($color)
    {
        $this->color = $color;

        return $this;
    }

    /**
     * Get color
     *
     * @return string 
     */
    public function getColor()
    {
        return $this->color;
    }

    /**
     * Set isActive
     *
     * @param boolean $isActive
     * @return Groups
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
     * Set codigousuarios
     *
     * @param string $codigousuarios
     * @return Groups
     */
    public function setCodigousuarios($codigousuarios)
    {
        $this->codigousuarios = $codigousuarios;

        return $this;
    }

    /**
     * Get codigousuarios
     *
     * @return string 
     */
    public function getCodigousuarios()
    {
        return $this->codigousuarios;
    }

    /**
     * Set permisos
     *
     * @param string $permisos
     * @return Groups
     */
    public function setPermisos($permisos)
    {
        $this->permisos = $permisos;

        return $this;
    }

    /**
     * Get permisos
     *
     * @return string 
     */
    public function getPermisos()
    {
        return $this->permisos;
    }

    /**
     * Set codigo
     *
     * @param string $codigo
     * @return Groups
     */
    public function setCodigo($codigo)
    {
        $this->codigo = $codigo;

        return $this;
    }

    /**
     * Get codigo
     *
     * @return string 
     */
    public function getCodigo()
    {
        return $this->codigo;
    }

    /**
     * Set tipo
     *
     * @param string $tipo
     * @return Groups
     */
    public function setTipo($tipo)
    {
        $this->tipo = $tipo;

        return $this;
    }

    /**
     * Get tipo
     *
     * @return string 
     */
    public function getTipo()
    {
        return $this->tipo;
    }

    /**
     * Set superior
     *
     * @param integer $superior
     * @return Groups
     */
    public function setSuperior($superior)
    {
        $this->superior = $superior;

        return $this;
    }

    /**
     * Get superior
     *
     * @return integer 
     */
    public function getSuperior()
    {
        return $this->superior;
    }

    /**
     * Add documentsSignatures
     *
     * @param \Nononsense\HomeBundle\Entity\DocumentsSignatures $documentsSignatures
     * @return Groups
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
     * @return Groups
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
     * Add groupsSubsecciones
     *
     * @param \Nononsense\UserBundle\Entity\GroupsSubsecciones $groupsSubsecciones
     * @return Groups
     */
    public function addGroupsSubseccione(\Nononsense\UserBundle\Entity\GroupsSubsecciones $groupsSubsecciones)
    {
        $this->groupsSubsecciones[] = $groupsSubsecciones;

        return $this;
    }

    /**
     * Remove groupsSubsecciones
     *
     * @param \Nononsense\UserBundle\Entity\GroupsSubsecciones $groupsSubsecciones
     */
    public function removeGroupsSubseccione(\Nononsense\UserBundle\Entity\GroupsSubsecciones $groupsSubsecciones)
    {
        $this->groupsSubsecciones->removeElement($groupsSubsecciones);
    }

    /**
     * Get groupsSubsecciones
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getGroupsSubsecciones()
    {
        return $this->groupsSubsecciones;
    }

    /**
     * Add contractsSignatures
     *
     * @param \Nononsense\HomeBundle\Entity\ContractsSignatures $contractsSignatures
     * @return Groups
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
     * @return Groups
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
     * Add tmWorkflows
     *
     * @param \Nononsense\HomeBundle\Entity\TMWorkflow $tmWorkflows
     * @return Groups
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
     * Add areas
     *
     * @param \Nononsense\HomeBundle\Entity\AreasGroups $areas
     * @return Groups
     */
    public function addArea(\Nononsense\HomeBundle\Entity\AreasGroups $areas)
    {
        $this->areas[] = $areas;

        return $this;
    }

    /**
     * Remove areas
     *
     * @param \Nononsense\HomeBundle\Entity\AreasGroups $areas
     */
    public function removeArea(\Nononsense\HomeBundle\Entity\AreasGroups $areas)
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
     * Add cvWorkflows
     *
     * @param \Nononsense\HomeBundle\Entity\CVWorkflow $cvWorkflows
     * @return Groups
     */
    public function addCvWorkflow(\Nononsense\HomeBundle\Entity\CVWorkflow $cvWorkflows)
    {
        $this->cvWorkflows[] = $cvWorkflows;

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
     * Add cvSecondWorkflows
     *
     * @param \Nononsense\HomeBundle\Entity\CVSecondWorkflow $cvSecondWorkflows
     * @return Groups
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
     * Add specificGroups
     *
     * @param \Nononsense\HomeBundle\Entity\SpecificGroups $specificGroups
     * @return Groups
     */
    public function addSpecificGroup(\Nononsense\HomeBundle\Entity\SpecificGroups $specificGroups)
    {
        $this->specificGroups[] = $specificGroups;

        return $this;
    }

    /**
     * Remove specificGroups
     *
     * @param \Nononsense\HomeBundle\Entity\SpecificGroups $specificGroups
     */
    public function removeSpecificGroup(\Nononsense\HomeBundle\Entity\SpecificGroups $specificGroups)
    {
        $this->specificGroups->removeElement($specificGroups);
    }

    /**
     * Get specificGroups
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getSpecificGroups()
    {
        return $this->specificGroups;
    }
}
