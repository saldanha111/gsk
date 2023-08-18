<?php

namespace Nononsense\HomeBundle\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Nononsense\UserBundle\Entity\Users;

/**
 * @ORM\Entity
 * @ORM\Table(name="mc_materials")
 * @ORM\Entity(repositoryClass="Nononsense\HomeBundle\Entity\MaterialCleanMaterialsRepository")
 * @ORM\HasLifecycleCallbacks
 */
class MaterialCleanMaterials
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
     * @ORM\ManyToOne(targetEntity="\Nononsense\UserBundle\Entity\Users", inversedBy="materialUpdated")
     * @ORM\JoinColumn(name="update_user", referencedColumnName="id", nullable=true)
     */
    protected $updateUser;

    /**
     * @ORM\ManyToOne(targetEntity="\Nononsense\UserBundle\Entity\Users", inversedBy="materialValidated")
     * @ORM\JoinColumn(name="validate_user", referencedColumnName="id", nullable=true)
     */
    protected $validateUser;

    /**
     * @ORM\OneToMany(targetEntity="\Nononsense\HomeBundle\Entity\MaterialCleanCodes", mappedBy="idMaterial")
     */
    private $barcode;

    /**
     * @ORM\OneToMany(targetEntity="\Nononsense\HomeBundle\Entity\MaterialCleanCleans", mappedBy="material")
     */
    private $cleans;

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
     * @ORM\ManyToOne(targetEntity="\Nononsense\HomeBundle\Entity\MaterialCleanProducts", inversedBy="material")
     * @ORM\JoinColumn(name="id_product", referencedColumnName="id")
     */
    private $product;

    /**
     * @ORM\ManyToOne(targetEntity="\Nononsense\HomeBundle\Entity\MaterialCleanCenters", inversedBy="material")
     * @ORM\JoinColumn(name="id_center", referencedColumnName="id")
     */
    private $center;

    public function __construct()
    {
        $this->created = new DateTime();
        $this->updated = new DateTime();
        $this->expirationDays = 30;
        $this->expirationHours = 0;
        $this->active = 1;
        $this->validated = 0;
        $this->additionalInfo = 0;
        $this->otherName = false;
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
     * @return MaterialCleanMaterials
     */
    public function setName(string $name): MaterialCleanMaterials
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
     * @return MaterialCleanMaterials
     */
    public function setExpirationDays(?int $expirationDays): MaterialCleanMaterials
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
     * @return MaterialCleanMaterials
     */
    public function setExpirationHours(?int $expirationHours): MaterialCleanMaterials
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
     * @return MaterialCleanMaterials
     */
    public function setCreated(DateTime $created): MaterialCleanMaterials
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
     * @return MaterialCleanMaterials
     */
    public function setActive(?bool $active): MaterialCleanMaterials
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
     * Add barcode
     *
     * @param MaterialCleanCodes $barcode
     * @return MaterialCleanMaterials
     */
    public function addBarcode(MaterialCleanCodes $barcode): MaterialCleanMaterials
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
     * @return ArrayCollection
     */
    public function getBarcode(): ArrayCollection
    {
        return $this->barcode;
    }

    /**
     * Add cleans
     *
     * @param MaterialCleanCleans $cleans
     * @return MaterialCleanMaterials
     */
    public function addClean(MaterialCleanCleans $cleans): MaterialCleanMaterials
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
     * Set product
     *
     * @param MaterialCleanProducts|null $product
     * @return MaterialCleanMaterials
     */
    public function setProduct(?MaterialCleanProducts $product): MaterialCleanMaterials
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
     * @return MaterialCleanMaterials
     */
    public function setAdditionalInfo(?bool $additionalInfo): MaterialCleanMaterials
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
     * @return MaterialCleanMaterials
     */
    public function setOtherName(?bool $otherName): MaterialCleanMaterials
    {
        $this->otherName = ($otherName)?: false;
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
     * @return MaterialCleanMaterials
     */
    public function setCenter(MaterialCleanCenters $center = null): MaterialCleanMaterials
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
     * @return MaterialCleanMaterials
     */
    public function setValidated(?bool $validated): MaterialCleanMaterials
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
     * @return MaterialCleanMaterials
     */
    public function setUpdated(DateTime $updated): MaterialCleanMaterials
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
     * @return MaterialCleanMaterials
     */
    public function setUpdateUser(?Users $updateUser): MaterialCleanMaterials
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
     * @return MaterialCleanMaterials
     */
    public function setValidateUser(?Users $validateUser): MaterialCleanMaterials
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
     * @return MaterialCleanMaterials
     */
    public function setUpdateComment(?string $updateComment): MaterialCleanMaterials
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
