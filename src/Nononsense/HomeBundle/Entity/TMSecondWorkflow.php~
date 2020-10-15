<?php
namespace Nononsense\HomeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity
 * @ORM\Table(name="tm_second_workflow")
 * @ORM\Entity(repositoryClass="Nononsense\HomeBundle\Entity\TMSecondWorkflowRepository")
 * @ORM\HasLifecycleCallbacks
 */
class TMSecondWorkflow
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="\Nononsense\HomeBundle\Entity\TMTemplates", inversedBy="tmSecondWorkflows")
     * @ORM\JoinColumn(name="template_id", referencedColumnName="id")
     */
    protected $template;

    /**
     * @ORM\ManyToOne(targetEntity="\Nononsense\HomeBundle\Entity\TMCumplimentations", inversedBy="tmSecondWorkflows")
     * @ORM\JoinColumn(name="cumplimentation_id", referencedColumnName="id")
     */
    protected $tmCumplimentation;

    /**
     * @var integer
     *
     * @ORM\Column(name="signatures_number", type="integer")
     */
    protected $signaturesNumber;

    
    
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
     * Set signaturesNumber
     *
     * @param integer $signaturesNumber
     * @return TMSecondWorkflow
     */
    public function setSignaturesNumber($signaturesNumber)
    {
        $this->signaturesNumber = $signaturesNumber;

        return $this;
    }

    /**
     * Get signaturesNumber
     *
     * @return integer 
     */
    public function getSignaturesNumber()
    {
        return $this->signaturesNumber;
    }

    /**
     * Set template
     *
     * @param \Nononsense\HomeBundle\Entity\TMTemplates $template
     * @return TMSecondWorkflow
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
     * Set tmCumplimentation
     *
     * @param \Nononsense\HomeBundle\Entity\TMCumplimentations $tmCumplimentation
     * @return TMSecondWorkflow
     */
    public function setTmCumplimentation(\Nononsense\HomeBundle\Entity\TMCumplimentations $tmCumplimentation = null)
    {
        $this->tmCumplimentation = $tmCumplimentation;

        return $this;
    }

    /**
     * Get tmCumplimentation
     *
     * @return \Nononsense\HomeBundle\Entity\TMCumplimentations 
     */
    public function getTmCumplimentation()
    {
        return $this->tmCumplimentation;
    }
}
