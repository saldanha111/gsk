<?php

namespace Nononsense\HomeBundle\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Nononsense\UserBundle\Entity\Users;

/**
 * @ORM\Entity
 * @ORM\Table(name="mc_centers_log")
 * @ORM\Entity(repositoryClass="Nononsense\HomeBundle\Entity\MaterialCleanCentersLogRepository")
 * @ORM\HasLifecycleCallbacks
 */
class MaterialCleanCentersLog
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="\Nononsense\HomeBundle\Entity\MaterialCleanCenters", inversedBy="log")
     * @ORM\JoinColumn(name="center", referencedColumnName="id", nullable=false)
     */
    protected $center;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255,  nullable=false)
     */
    protected $name;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text")
     */
    protected $description;

    /**
     * @ORM\Column(name="created", type="datetime", nullable=false, options={"default":"CURRENT_TIMESTAMP"})
     */
    protected $created;

    /**
     * @var bool
     *
     * @ORM\Column(name="active", type="boolean", nullable=false, options={"default":1})
     */
    private $active;

    /**
     * @var bool
     *
     * @ORM\Column(name="validated", type="boolean", nullable=false, options={"default":1})
     */
    private $validated;

    /**
     * @ORM\Column(name="updated", type="datetime", nullable=false, options={"default":"CURRENT_TIMESTAMP"})
     */
    private $updated;

    /**
     * @var string
     *
     * @ORM\Column(name="update_comment", type="text", nullable=true, options={"default":NULL})
     */
    protected $updateComment;

    /**
     * @ORM\ManyToOne(targetEntity="\Nononsense\UserBundle\Entity\Users", inversedBy="centerUpdatedLog")
     * @ORM\JoinColumn(name="update_user", referencedColumnName="id", nullable=true)
     */
    protected $updateUser;

    /**
     * @ORM\ManyToOne(targetEntity="\Nononsense\UserBundle\Entity\Users", inversedBy="centerValidatedLog")
     * @ORM\JoinColumn(name="validate_user", referencedColumnName="id", nullable=true)
     */
    protected $validateUser;

    /**
     * @ORM\ManyToOne(targetEntity="\Nononsense\HomeBundle\Entity\MaterialCleanDepartments", inversedBy="centerLog")
     * @ORM\JoinColumn(name="department", referencedColumnName="id")
     */
    protected $department;

    /**
     * Get id
     *
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return MaterialCleanCentersLog
     */
    public function setName(string $name): MaterialCleanCentersLog
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Set center
     *
     * @param MaterialCleanCenters $center
     * @return MaterialCleanCentersLog
     */
    public function setCenter(MaterialCleanCenters $center): MaterialCleanCentersLog
    {
        $this->center = $center;
        return $this;
    }

    /**
     * Get center
     *
     * @return MaterialCleanCenters
     */
    public function getCenter(): MaterialCleanCenters
    {
        return $this->center;
    }

    /**
     * Get name
     *
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * Set description
     *
     * @param string $description
     * @return MaterialCleanCentersLog
     */
    public function setDescription(string $description): MaterialCleanCentersLog
    {
        $this->description = $description;
        return $this;
    }

    /**
     * Get description
     *
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * Set created
     *
     * @param DateTime $created
     * @return MaterialCleanCentersLog
     */
    public function setCreated(DateTime $created): MaterialCleanCentersLog
    {
        $this->created = $created;
        return $this;
    }

    /**
     * Get created
     *
     * @return DateTime
     */
    public function getCreated(): DateTime
    {
        return $this->created;
    }

    /**
     * Set active
     *
     * @param bool $active
     * @return MaterialCleanCentersLog
     */
    public function setActive(bool $active): MaterialCleanCentersLog
    {
        $this->active = ($active)?: false;
        return $this;
    }

    /**
     * Get active
     *
     * @return bool
     */
    public function getActive(): bool
    {
        return $this->active;
    }

    /**
     * Set department
     *
     * @param MaterialCleanDepartments|null $department
     * @return MaterialCleanCentersLog
     */
    public function setDepartment(?MaterialCleanDepartments $department): MaterialCleanCentersLog
    {
        $this->department = $department;
        return $this;
    }

    /**
     * Get department
     *
     * @return MaterialCleanDepartments|null
     */
    public function getDepartment(): ?MaterialCleanDepartments
    {
        return $this->department;
    }

    /**
     * Set validated
     *
     * @param bool $validated
     * @return MaterialCleanCentersLog
     */
    public function setValidated(bool $validated): MaterialCleanCentersLog
    {
        $this->validated = ($validated)?: false;
        return $this;
    }

    /**
     * Get validated
     *
     * @return bool
     */
    public function getValidated(): bool
    {
        return $this->validated;
    }

    /**
     * Set updated
     *
     * @param DateTime $updated
     * @return MaterialCleanCentersLog
     */
    public function setUpdated(DateTime $updated): MaterialCleanCentersLog
    {
        $this->updated = $updated;
        return $this;
    }

    /**
     * Get updated
     *
     * @return DateTime
     */
    public function getUpdated(): DateTime
    {
        return $this->updated;
    }

    /**
     * Set updateUser
     *
     * @param Users|null $updateUser
     * @return MaterialCleanCentersLog
     */
    public function setUpdateUser(?Users $updateUser): MaterialCleanCentersLog
    {
        $this->updateUser = $updateUser;
        return $this;
    }

    /**
     * Get updateUser
     *
     * @return Users|null
     */
    public function getUpdateUser(): ?Users
    {
        return $this->updateUser;
    }

    /**
     * Set validateUser
     *
     * @param Users|null $validateUser
     * @return MaterialCleanCentersLog
     */
    public function setValidateUser(?Users $validateUser): MaterialCleanCentersLog
    {
        $this->validateUser = $validateUser;
        return $this;
    }

    /**
     * Get validateUser
     *
     * @return Users|null
     */
    public function getValidateUser(): ?Users
    {
        return $this->validateUser;
    }

    /**
     * Set updateComment
     *
     * @param string|null $updateComment
     * @return MaterialCleanCentersLog
     */
    public function setUpdateComment(?string $updateComment): MaterialCleanCentersLog
    {
        $this->updateComment = $updateComment;
        return $this;
    }

    /**
     * Get updateComment
     *
     * @return string|null
     */
    public function getupdateComment(): ?string
    {
        return $this->updateComment;
    }
}
