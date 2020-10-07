<?php
namespace Nononsense\HomeBundle\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="rc_signatures")
 * @ORM\Entity(repositoryClass="Nononsense\HomeBundle\Entity\RCSignaturesRepository")
 * @ORM\HasLifecycleCallbacks
 */
class RCSignatures
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="\Nononsense\HomeBundle\Entity\RetentionCategories", inversedBy="rcSignatures")
     * @ORM\JoinColumn(name="rc_id", referencedColumnName="id")
     */
    protected $retentionCategory;

    /**
     * @ORM\Column(name="action", type="string", length=255)
     */
    protected $action;

    /**
     * @ORM\ManyToOne(targetEntity="\Nononsense\UserBundle\Entity\Users", inversedBy="rcSignatures")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    protected $userEntiy;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    protected $description;

    /**
     * @var string
     *
     * @ORM\Column(name="signature", type="text")
     */
    protected $signature;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $modified;

    public function __construct()
    {
        $this->modified = new DateTime();
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
     * Set action
     *
     * @param string $action
     * @return RCSignatures
     */
    public function setAction($action)
    {
        $this->action = $action;

        return $this;
    }

    /**
     * Get action
     *
     * @return string 
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * Set description
     *
     * @param string $description
     * @return RCSignatures
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
     * Set signature
     *
     * @param string $signature
     * @return RCSignatures
     */
    public function setSignature($signature)
    {
        $this->signature = $signature;

        return $this;
    }

    /**
     * Get signature
     *
     * @return string 
     */
    public function getSignature()
    {
        return $this->signature;
    }

    /**
     * Set modified
     *
     * @param \DateTime $modified
     * @return RCSignatures
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
     * Set retentionCategory
     *
     * @param \Nononsense\HomeBundle\Entity\RetentionCategories $retentionCategory
     * @return RCSignatures
     */
    public function setRetentionCategory(\Nononsense\HomeBundle\Entity\RetentionCategories $retentionCategory = null)
    {
        $this->retentionCategory = $retentionCategory;

        return $this;
    }

    /**
     * Get retentionCategory
     *
     * @return \Nononsense\HomeBundle\Entity\RetentionCategories 
     */
    public function getRetentionCategory()
    {
        return $this->retentionCategory;
    }

    /**
     * Set userEntiy
     *
     * @param \Nononsense\UserBundle\Entity\Users $userEntiy
     * @return RCSignatures
     */
    public function setUserEntiy(\Nononsense\UserBundle\Entity\Users $userEntiy = null)
    {
        $this->userEntiy = $userEntiy;

        return $this;
    }

    /**
     * Get userEntiy
     *
     * @return \Nononsense\UserBundle\Entity\Users 
     */
    public function getUserEntiy()
    {
        return $this->userEntiy;
    }
}
