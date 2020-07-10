<?php
namespace Nononsense\HomeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity
 * @ORM\Table(name="tm_signatures")
 * @ORM\Entity(repositoryClass="Nononsense\HomeBundle\Entity\TMSignaturesRepository")
 * @ORM\HasLifecycleCallbacks
 */
class TMSignatures
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="\Nononsense\HomeBundle\Entity\TMTemplates", inversedBy="tmSignatures")
     * @ORM\JoinColumn(name="template_id", referencedColumnName="id")
     */
    protected $template;

    /**
     * @ORM\ManyToOne(targetEntity="\Nononsense\HomeBundle\Entity\TMActions", inversedBy="tmSignatures")
     * @ORM\JoinColumn(name="action_id", referencedColumnName="id")
     */
    protected $action;

    /**
     * @var string
     *
     * @ORM\Column(name="signature", type="text")
     */
    protected $signature;

    /**
     * @ORM\ManyToOne(targetEntity="\Nononsense\UserBundle\Entity\Users", inversedBy="tmSignatures")
     * @ORM\JoinColumn(name="usercreatedid", referencedColumnName="id")
     */
    protected $userEntiy;

    /**
     * @var string
     *
     * @ORM\Column(name="version", type="string")
     */
    protected $version;

    /**
     * @var string
     *
     * @ORM\Column(name="configuration", type="string")
     */
    protected $configuration;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $created;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $modified;

    
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
     * Set created
     *
     * @param \DateTime $created
     * @return TMSignatures
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
     * @return TMSignatures
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
     * Set signature
     *
     * @param string $signature
     * @return TMSignatures
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
     * Set version
     *
     * @param string $version
     * @return TMSignatures
     */
    public function setVersion($version)
    {
        $this->version = $version;

        return $this;
    }

    /**
     * Get version
     *
     * @return string 
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * Set configuration
     *
     * @param string $configuration
     * @return TMSignatures
     */
    public function setConfiguration($configuration)
    {
        $this->configuration = $configuration;

        return $this;
    }

    /**
     * Get configuration
     *
     * @return string 
     */
    public function getConfiguration()
    {
        return $this->configuration;
    }

    /**
     * Set template
     *
     * @param \Nononsense\HomeBundle\Entity\TMTemplates $template
     * @return TMSignatures
     */
    public function setTemplate(\Nononsense\HomeBundle\Entity\TMTemplates $template = null)
    {
        $this->template = $template;

        return $this;
    }

    /**
     * Get template
     *
     * @return \Nononsense\HomeBundle\Entity\TMTemplates 
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * Set action
     *
     * @param \Nononsense\HomeBundle\Entity\TMActions $action
     * @return TMSignatures
     */
    public function setAction(\Nononsense\HomeBundle\Entity\TMActions $action = null)
    {
        $this->action = $action;

        return $this;
    }

    /**
     * Get action
     *
     * @return \Nononsense\HomeBundle\Entity\TMActions 
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * Set userEntiy
     *
     * @param \Nononsense\UserBundle\Entity\Users $userEntiy
     * @return TMSignatures
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
