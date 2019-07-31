<?php
/**
 * Created by IntelliJ IDEA.
 * User: gushe
 * Date: 26/06/2018
 * Time: 12:55
 */

namespace Nononsense\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="userssection")
 * @ORM\Entity(repositoryClass="Nononsense\UserBundle\Entity\UsersSectionRepository")
 * @ORM\HasLifecycleCallbacks
 */
class UsersSection
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var integer
     *
     * @ORM\Column(name="user_id", type="integer")
     */
    protected $user_id;

    /**
     * @var integer
     *
     * @ORM\Column(name="section_id", type="integer")
     */
    protected $section_id;

    /**
     * @ORM\Column(type="date")
     */
    protected $created;

    /**
     * @ORM\Column(type="date")
     */
    protected $modified;

    /**
     * @ORM\ManyToOne(targetEntity="\Nononsense\UserBundle\Entity\Users", inversedBy="sections")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */

    protected $user;

    /**
     * @ORM\ManyToOne(targetEntity="\Nononsense\UserBundle\Entity\Sections", inversedBy="users")
     * @ORM\JoinColumn(name="section_id", referencedColumnName="id")
     */
    protected $section;

    /**
     * Userssection constructor.
     */
    public function __construct()
    {

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
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set user_id
     *
     * @param integer $userId
     * @return Userssection
     */
    public function setUserId($userId)
    {
        $this->user_id = $userId;

        return $this;
    }

    /**
     * Get user_id
     *
     * @return integer 
     */
    public function getUserId()
    {
        return $this->user_id;
    }

    /**
     * Set section_id
     *
     * @param integer $sectionId
     * @return Userssection
     */
    public function setSectionId($sectionId)
    {
        $this->section_id = $sectionId;

        return $this;
    }

    /**
     * Get section_id
     *
     * @return integer 
     */
    public function getSectionId()
    {
        return $this->section_id;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     * @return Userssection
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
     * @return Userssection
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
     * Set user
     *
     * @param \Nononsense\UserBundle\Entity\Users $user
     * @return Userssection
     */
    public function setUser(\Nononsense\UserBundle\Entity\Users $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \Nononsense\UserBundle\Entity\Users
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set section
     *
     * @param \Nononsense\UserBundle\Entity\Sections $section
     * @return Userssection
     */
    public function setSection(\Nononsense\UserBundle\Entity\Sections $section = null)
    {
        $this->section = $section;

        return $this;
    }

    /**
     * Get section
     *
     * @return \Nononsense\UserBundle\Entity\Sections
     */
    public function getSection()
    {
        return $this->section;
    }
}
