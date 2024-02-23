<?php

namespace Nononsense\HomeBundle\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Nononsense\UserBundle\Entity\Users;

/**
 * @ORM\Entity
 * @ORM\Table(name="mc_materials_log")
 * @ORM\Entity(repositoryClass="Nononsense\HomeBundle\Entity\MaterialCleanMaterialsLogRepository")
 * @ORM\HasLifecycleCallbacks
 */
class MaterialCleanMaterialsLog
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="\Nononsense\HomeBundle\Entity\MaterialCleanMaterials", inversedBy="log")
     * @ORM\JoinColumn(name="material", referencedColumnName="id", nullable=false)
     */
    protected $material;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255,  nullable=false)
     */
    protected $name;

    /**
     * @var int | null
     *
     * @ORM\Column(name="expiration_days", type="integer", options={"default":0}, nullable=true)
     */
    protected $expirationDays;

    /**
     * @var int | null
     *
     * @ORM\Column(name="expiration_hours", type="integer", options={"default":0}, nullable=true)
     */
    protected $expirationHours;

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
     * @ORM\ManyToOne(targetEntity="\Nononsense\UserBundle\Entity\Users", inversedBy="materialUpdatedLog")
     * @ORM\JoinColumn(name="update_user", referencedColumnName="id", nullable=true)
     */
    protected $updateUser;

    /**
     * @ORM\ManyToOne(targetEntity="\Nononsense\UserBundle\Entity\Users", inversedBy="materialValidatedLog")
     * @ORM\JoinColumn(name="validate_user", referencedColumnName="id", nullable=true)
     */
    protected $validateUser;

    /**
     * @var bool
     *
     * @ORM\Column(name="additional_info", type="boolean")
     */
    private $additionalInfo;

    /**
     * @var bool
     *
     * @ORM\Column(name="other_name", type="boolean", nullable=false, options={"default":0})
     */
    private $otherName;

    /**
     * @var string
     *
     * @ORM\Column(name="update_comment", type="text", nullable=true, options={"default":NULL})
     */
    protected $updateComment;

    /**
     * @ORM\ManyToOne(targetEntity="\Nononsense\HomeBundle\Entity\MaterialCleanProducts", inversedBy="materialLog")
     * @ORM\JoinColumn(name="id_product", referencedColumnName="id")
     */
    private $product;

    /**
     * @ORM\ManyToOne(targetEntity="\Nononsense\HomeBundle\Entity\MaterialCleanCenters", inversedBy="materialLog")
     * @ORM\JoinColumn(name="id_center", referencedColumnName="id")
     */
    private $center;

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
     * Set material
     *
     * @param MaterialCleanMaterials $material
     * @return MaterialCleanMaterialsLog
     */
    public function setMaterial(MaterialCleanMaterials $material): MaterialCleanMaterialsLog
    {
        $this->material = $material;
        return $this;
    }

    /**
     * Get material
     *
     * @return MaterialCleanMaterials
     */
    public function getMaterial(): MaterialCleanMaterials
    {
        return $this->material;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return MaterialCleanMaterialsLog
     */
    public function setName(string $name): MaterialCleanMaterialsLog
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
     * Set expirationDays
     *
     * @param int|null $expirationDays
     * @return MaterialCleanMaterialsLog
     */
    public function setExpirationDays(?int $expirationDays): MaterialCleanMaterialsLog
    {
        $this->expirationDays = $expirationDays;
        return $this;
    }

    /**
     * Get expirationDays
     *
     * @return int
     */
    public function getExpirationDays(): ?int
    {
        return $this->expirationDays;
    }

    /**
     * Set expirationHours
     *
     * @param int|null $expirationHours
     * @return MaterialCleanMaterialsLog
     */
    public function setExpirationHours(?int $expirationHours): MaterialCleanMaterialsLog
    {
        $this->expirationHours = $expirationHours;
        return $this;
    }

    /**
     * Get expirationHours
     *
     * @return int|null
     */
    public function getExpirationHours(): ?int
    {
        return $this->expirationHours;
    }

    /**
     * Set created
     *
     * @param DateTime $created
     * @return MaterialCleanMaterialsLog
     */
    public function setCreated(DateTime $created): MaterialCleanMaterialsLog
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
     * @return MaterialCleanMaterialsLog
     */
    public function setActive(bool $active): MaterialCleanMaterialsLog
    {
        $this->active = ($active)?: false;
        return $this;
    }

    /**
     * Get active
     *
     * @return bool
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * Set product
     *
     * @param MaterialCleanProducts|null $product
     * @return MaterialCleanMaterialsLog
     */
    public function setProduct(?MaterialCleanProducts $product): MaterialCleanMaterialsLog
    {
        $this->product = $product;
        return $this;
    }

    /**
     * Get product
     *
     * @return MaterialCleanProducts|null
     */
    public function getProduct(): ?MaterialCleanProducts
    {
        return $this->product;
    }

    /**
     * Set additionalInfo
     *
     * @param bool|null $additionalInfo
     * @return MaterialCleanMaterialsLog
     */
    public function setAdditionalInfo(?bool $additionalInfo): MaterialCleanMaterialsLog
    {
        $this->additionalInfo = ($additionalInfo)?: false;
        return $this;
    }

    /**
     * Get additionalInfo
     *
     * @return bool
     */
    public function getAdditionalInfo(): bool
    {
        return $this->additionalInfo;
    }

    /**
     * Set otherName
     *
     * @param bool $otherName
     * @return MaterialCleanMaterialsLog
     */
    public function setOtherName(bool $otherName): MaterialCleanMaterialsLog
    {
        $this->otherName = $otherName;
        return $this;
    }

    /**
     * Get otherName
     *
     * @return bool
     */
    public function getOtherName(): bool
    {
        return $this->otherName;
    }

    /**
     * Set center
     *
     * @param MaterialCleanCenters|null $center
     * @return MaterialCleanMaterialsLog
     */
    public function setCenter(MaterialCleanCenters $center = null): MaterialCleanMaterialsLog
    {
        $this->center = $center;
        return $this;
    }

    /**
     * Get center
     *
     * @return MaterialCleanCenters|null
     */
    public function getCenter(): ?MaterialCleanCenters
    {
        return $this->center;
    }

    /**
     * Set validated
     *
     * @param bool $validated
     * @return MaterialCleanMaterialsLog
     */
    public function setValidated(bool $validated): MaterialCleanMaterialsLog
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
     * @return MaterialCleanMaterialsLog
     */
    public function setUpdated(DateTime $updated): MaterialCleanMaterialsLog
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
     * @return MaterialCleanMaterialsLog
     */
    public function setUpdateUser(?Users $updateUser): MaterialCleanMaterialsLog
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
     * @return MaterialCleanMaterialsLog
     */
    public function setValidateUser(?Users $validateUser): MaterialCleanMaterialsLog
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
     * @return MaterialCleanMaterialsLog
     */
    public function setUpdateComment(?string $updateComment): MaterialCleanMaterialsLog
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
