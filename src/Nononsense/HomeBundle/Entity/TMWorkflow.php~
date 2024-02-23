<?php
namespace Nononsense\HomeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity
 * @ORM\Table(name="tm_workflow")
 * @ORM\Entity(repositoryClass="Nononsense\HomeBundle\Entity\TMWorkflowRepository")
 * @ORM\HasLifecycleCallbacks
 */
class TMWorkflow
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="\Nononsense\HomeBundle\Entity\TMTemplates", inversedBy="tmWorkflows")
     * @ORM\JoinColumn(name="template_id", referencedColumnName="id")
     */
    protected $template;

    /**
     * @ORM\ManyToOne(targetEntity="\Nononsense\HomeBundle\Entity\TMActions", inversedBy="tmWorkflows")
     * @ORM\JoinColumn(name="action_id", referencedColumnName="id")
     */
    protected $action;

    /**
     * @var integer
     *
     * @ORM\Column(name="number", type="integer")
     */
    protected $number;

    /**
     * @ORM\ManyToOne(targetEntity="\Nononsense\UserBundle\Entity\Users", inversedBy="tmWorkflows")
     * @ORM\JoinColumn(name="userid", referencedColumnName="id")
     * @Groups({"list_baseS"})
     */
    protected $userEntiy;

    /**
     * @ORM\ManyToOne(targetEntity="\Nononsense\GroupBundle\Entity\Groups", inversedBy="tmWorkflows")
     * @ORM\JoinColumn(name="groupid", referencedColumnName="id")
     * @Groups({"list_baseS"})
     */
    protected $groupEntiy;

    /**
     * @var boolean $signed
     *
     * @ORM\Column(name="signed", type="boolean",  options={"default" = 0})
     * @Groups({"list_baseS"})
     */
    protected $signed;

    /**
     * @ORM\OneToMany(targetEntity="\Nononsense\HomeBundle\Entity\TMSignatures", mappedBy="tmWhoAprobFromWorkflow")
     */
    protected $tmSignatures;

    
    public function __construct()
    {

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
     * Set number
     *
     * @param integer $number
     * @return TMWorkflow
     */
    public function setNumber($number)
    {
        $this->number = $number;

        return $this;
    }

    /**
     * Get number
     *
     * @return integer 
     */
    public function getNumber()
    {
        return $this->number;
    }

    /**
     * Set template
     *
     * @param \Nononsense\HomeBundle\Entity\TMTemplates $template
     * @return TMWorkflow
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
     * @return TMWorkflow
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
     * @return TMWorkflow
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

    /**
     * Set groupEntiy
     *
     * @param \Nononsense\GroupBundle\Entity\Groups $groupEntiy
     * @return TMWorkflow
     */
    public function setGroupEntiy(\Nononsense\GroupBundle\Entity\Groups $groupEntiy = null)
    {
        $this->groupEntiy = $groupEntiy;

        return $this;
    }

    /**
     * Get groupEntiy
     *
     * @return \Nononsense\GroupBundle\Entity\Groups 
     */
    public function getGroupEntiy()
    {
        return $this->groupEntiy;
    }

    /**
     * Set signed
     *
     * @param boolean $signed
     * @return TMWorkflow
     */
    public function setSigned($signed)
    {
        $this->signed = $signed;

        return $this;
    }

    /**
     * Get signed
     *
     * @return boolean 
     */
    public function getSigned()
    {
        return $this->signed;
    }

    /**
     * Add tmSignatures
     *
     * @param \Nononsense\HomeBundle\Entity\TMSignatures $tmSignatures
     * @return TMWorkflow
     */
    public function addTmSignature(\Nononsense\HomeBundle\Entity\TMSignatures $tmSignatures)
    {
        $this->tmSignatures[] = $tmSignatures;

        return $this;
    }

    /**
     * Remove tmSignatures
     *
     * @param \Nononsense\HomeBundle\Entity\TMSignatures $tmSignatures
     */
    public function removeTmSignature(\Nononsense\HomeBundle\Entity\TMSignatures $tmSignatures)
    {
        $this->tmSignatures->removeElement($tmSignatures);
    }

    /**
     * Get tmSignatures
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getTmSignatures()
    {
        return $this->tmSignatures;
    }
}
