<?php

namespace Nononsense\HomeBundle\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Nononsense\UserBundle\Entity\Users;

/**
 * @ORM\Entity
 * @ORM\Table(name="mc_products_log")
 * @ORM\Entity(repositoryClass="Nononsense\HomeBundle\Entity\MaterialCleanProductsRepository")
 * @ORM\HasLifecycleCallbacks
 */
class MaterialCleanProductsLog
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="\Nononsense\HomeBundle\Entity\MaterialCleanProducts", inversedBy="log")
     * @ORM\JoinColumn(name="product", referencedColumnName="id", nullable=true)
     */
    protected $product;

    /**
     * @var string
     * @ORM\Column(name="name", type="string", length=255,  nullable=false)
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
     * Get id
     *
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Set product
     *
     * @param MaterialCleanProducts $product
     * @return MaterialCleanProductsLog
     */
    public function setProduct(MaterialCleanProducts $product): MaterialCleanProductsLog
    {
        $this->product = $product;
        return $this;
    }

    /**
     * Get product
     *
     * @return MaterialCleanProducts
     */
    public function getProduct(): MaterialCleanProducts
    {
        return $this->product;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return MaterialCleanProductsLog
     */
    public function setName(string $name): MaterialCleanProductsLog
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
     * @return MaterialCleanProductsLog
     */
    public function setTagsNumber(int $tagsNumber): MaterialCleanProductsLog
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
     * Set active
     *
     * @param bool $active
     * @return MaterialCleanProductsLog
     */
    public function setActive(bool $active): MaterialCleanProductsLog
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
     * @return MaterialCleanProductsLog
     */
    public function setCreated(DateTime $created): MaterialCleanProductsLog
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
     * @return MaterialCleanProductsLog
     */
    public function setValidated(bool $validated): MaterialCleanProductsLog
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
     * @return MaterialCleanProductsLog
     */
    public function setUpdated(DateTime $updated): MaterialCleanProductsLog
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
     * @return MaterialCleanProductsLog
     */
    public function setUpdateUser(?Users $updateUser): MaterialCleanProductsLog
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
     * @return MaterialCleanProductsLog
     */
    public function setValidateUser(?Users $validateUser): MaterialCleanProductsLog
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
     * @return MaterialCleanProductsLog
     */
    public function setUpdateComment(?string $updateComment): MaterialCleanProductsLog
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
