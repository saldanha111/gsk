<?php

namespace Nononsense\HomeBundle\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Nononsense\UserBundle\Entity\Users;

/**
 * @ORM\Entity
 * @ORM\Table(name="mc_centers")
 * @ORM\Entity(repositoryClass="Nononsense\HomeBundle\Entity\MaterialCleanCentersRepository")
 * @ORM\HasLifecycleCallbacks
 */
class MaterialCleanCenters
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
     * @ORM\Column(name="name", type="string", length=255,  nullable=false, unique=true)
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
     * @ORM\ManyToOne(targetEntity="\Nononsense\UserBundle\Entity\Users", inversedBy="centerUpdated")
     * @ORM\JoinColumn(name="update_user", referencedColumnName="id", nullable=true)
     */
    protected $updateUser;

    /**
     * @ORM\ManyToOne(targetEntity="\Nononsense\UserBundle\Entity\Users", inversedBy="centerValidated")
     * @ORM\JoinColumn(name="validate_user", referencedColumnName="id", nullable=true)
     */
    protected $validateUser;

    /**
     * @ORM\OneToMany(targetEntity="\Nononsense\HomeBundle\Entity\MaterialCleanCodes", mappedBy="idCenter")
     */
    private $barcode;

    /**
     * @ORM\OneToMany(targetEntity="\Nononsense\HomeBundle\Entity\MaterialCleanCleans", mappedBy="center")
     */
    private $cleans;

    /**
     * @ORM\OneToMany(targetEntity="\Nononsense\HomeBundle\Entity\MaterialCleanMaterials", mappedBy="center")
     */
    protected $material;

    /**
     * @ORM\ManyToOne(targetEntity="\Nononsense\HomeBundle\Entity\MaterialCleanDepartments", inversedBy="center")
     * @ORM\JoinColumn(name="department", referencedColumnName="id")
     */
    protected $department;

    public function __construct()
    {
        $this->created = new DateTime();
        $this->updated = new DateTime();
        $this->active = 1;
        $this->validated = 0;
    }

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
     * @return MaterialCleanCenters
     */
    public function setName(string $name): MaterialCleanCenters
    {
        $this->name = $name;
        return $this;
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
     * @return MaterialCleanCenters
     */
    public function setDescription(string $description): MaterialCleanCenters
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
     * @return MaterialCleanCenters
     */
    public function setCreated(DateTime $created): MaterialCleanCenters
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
     * @return MaterialCleanCenters
     */
    public function setActive(bool $active): MaterialCleanCenters
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
     * Add barcode
     *
     * @param MaterialCleanCodes $barcode
     * @return MaterialCleanCenters
     */
    public function addBarcode(MaterialCleanCodes $barcode): MaterialCleanCenters
    {
        $this->barcode[] = $barcode;
        return $this;
    }

    /**
     * Remove barcode
     *
     * @param MaterialCleanCodes $barcode
     */
    public function removeBarcode(MaterialCleanCodes $barcode): void
    {
        $this->barcode->removeElement($barcode);
    }

    /**
     * Get barcode
     *
     * @return ArrayCollection;
     */
    public function getBarcode(): ArrayCollection
    {
        return $this->barcode;
    }

    /**
     * Add cleans
     *
     * @param MaterialCleanCleans $cleans
     * @return MaterialCleanCenters
     */
    public function addClean(MaterialCleanCleans $cleans): MaterialCleanCenters
    {
        $this->cleans[] = $cleans;
        return $this;
    }

    /**
     * Remove cleans
     *
     * @param MaterialCleanCleans $cleans
     */
    public function removeClean(MaterialCleanCleans $cleans): void
    {
        $this->cleans->removeElement($cleans);
    }

    /**
     * Get cleans
     *
     * @return ArrayCollection
     */
    public function getCleans(): ArrayCollection
    {
        return $this->cleans;
    }

    /**
     * Add material
     *
     * @param MaterialCleanMaterials $material
     * @return MaterialCleanCenters
     */
    public function addMaterial(MaterialCleanMaterials $material): MaterialCleanCenters
    {
        $this->material[] = $material;
        return $this;
    }

    /**
     * Remove material
     *
     * @param MaterialCleanMaterials $material
     */
    public function removeMaterial(MaterialCleanMaterials $material): void
    {
        $this->material->removeElement($material);
    }

    /**
     * Get material
     *
     * @return ArrayCollection
     */
    public function getMaterial(): ArrayCollection
    {
        return $this->material;
    }


    /**
     * Set department
     *
     * @param MaterialCleanDepartments $department
     * @return MaterialCleanCenters
     */
    public function setDepartment(MaterialCleanDepartments $department): MaterialCleanCenters
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
     * @return MaterialCleanCenters
     */
    public function setValidated(bool $validated): MaterialCleanCenters
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
     * @return MaterialCleanCenters
     */
    public function setUpdated(DateTime $updated): MaterialCleanCenters
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
     * @return MaterialCleanCenters
     */
    public function setUpdateUser(?Users $updateUser): MaterialCleanCenters
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
     * @return MaterialCleanCenters
     */
    public function setValidateUser(?Users $validateUser): MaterialCleanCenters
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
}
