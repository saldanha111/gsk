<?php

namespace Nononsense\HomeBundle\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Nononsense\UserBundle\Entity\Users;

/**
 * @ORM\Entity
 * @ORM\Table(name="mc_products")
 * @ORM\Entity(repositoryClass="Nononsense\HomeBundle\Entity\MaterialCleanProductsRepository")
 * @ORM\HasLifecycleCallbacks
 */
class MaterialCleanProducts
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     * @ORM\Column(name="name", type="string", length=255,  nullable=false, unique=true)
     */
    protected $name;

    /**
     * @var int
     * @ORM\Column(name="tags_number", type="integer")
     */
    protected $tagsNumber;

    /**
     * @var bool
     *
     * @ORM\Column(name="active", type="boolean", nullable=false, options={"default":1})
     */
    private $active;

    /**
     * @ORM\Column(name="created", type="datetime", nullable=false, options={"default":"CURRENT_TIMESTAMP"})
     */
    protected $created;

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
     * @ORM\OneToMany(targetEntity="\Nononsense\HomeBundle\Entity\MaterialCleanMaterials", mappedBy="product")
     */
    protected $material;

    public function __construct()
    {
        $this->material = new ArrayCollection();
        $this->created = new DateTime();
        $this->updated = new DateTime();
        $this->active = true;
        $this->validated = false;
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
     * @return MaterialCleanProducts
     */
    public function setName(string $name): MaterialCleanProducts
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
     * Set tagsNumber
     *
     * @param int $tagsNumber
     * @return MaterialCleanProducts
     */
    public function setTagsNumber(int $tagsNumber): MaterialCleanProducts
    {
        $this->tagsNumber = $tagsNumber;
        return $this;
    }

    /**
     * Get tagsNumber
     *
     * @return int|null
     */
    public function getTagsNumber(): ?int
    {
        return $this->tagsNumber;
    }
    /**
     * Constructor
     */

    /**
     * Add material
     *
     * @param MaterialCleanMaterials $material
     * @return MaterialCleanProducts
     */
    public function addMaterial(MaterialCleanMaterials $material): MaterialCleanProducts
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
     * Set active
     *
     * @param bool $active
     * @return MaterialCleanProducts
     */
    public function setActive(?bool $active): MaterialCleanProducts
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
     * Set created
     *
     * @param DateTime $created
     * @return MaterialCleanProducts
     */
    public function setCreated(DateTime $created): MaterialCleanProducts
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
     * Set validated
     *
     * @param bool $validated
     * @return MaterialCleanProducts
     */
    public function setValidated(?bool $validated): MaterialCleanProducts
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
     * @return MaterialCleanProducts
     */
    public function setUpdated(DateTime $updated): MaterialCleanProducts
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
     * @return MaterialCleanProducts
     */
    public function setUpdateUser(?Users $updateUser): MaterialCleanProducts
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
     * @return MaterialCleanProducts
     */
    public function setValidateUser(?Users $validateUser): MaterialCleanProducts
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
     * @return MaterialCleanProducts
     */
    public function setUpdateComment(?string $updateComment): MaterialCleanProducts
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
