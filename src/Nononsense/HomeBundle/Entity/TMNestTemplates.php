<?php
namespace Nononsense\HomeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity
 * @ORM\Table(name="tm_nest_templates")
 * @ORM\Entity(repositoryClass="Nononsense\HomeBundle\Entity\TMNestTemplatesRepository")
 * @ORM\HasLifecycleCallbacks
 */
class TMNestTemplates
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="\Nononsense\HomeBundle\Entity\TMTemplates", inversedBy="tmNestMasterTemplates")
     * @ORM\JoinColumn(name="template_id", referencedColumnName="id")
     */
    protected $template;

    /**
     * @ORM\ManyToOne(targetEntity="\Nononsense\HomeBundle\Entity\TMTemplates", inversedBy="tmNestTemplates")
     * @ORM\JoinColumn(name="nest_template_id", referencedColumnName="id")
     */
    protected $nestTemplate;

    /**
     * @var integer
     *
     * @ORM\Column(name="nest_number", type="integer")
     */
    protected $nestNumber;

    
    
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
     * Set nestNumber
     *
     * @param integer $nestNumber
     * @return TMNestTemplates
     */
    public function setNestNumber($nestNumber)
    {
        $this->nestNumber = $nestNumber;

        return $this;
    }

    /**
     * Get nestNumber
     *
     * @return integer 
     */
    public function getNestNumber()
    {
        return $this->nestNumber;
    }

    /**
     * Set template
     *
     * @param \Nononsense\HomeBundle\Entity\TMTemplates $template
     * @return TMNestTemplates
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
     * Set nestTemplate
     *
     * @param \Nononsense\HomeBundle\Entity\TMTemplates $nestTemplate
     * @return TMNestTemplates
     */
    public function setNestTemplate(\Nononsense\HomeBundle\Entity\TMTemplates $nestTemplate = null)
    {
        $this->nestTemplate = $nestTemplate;

        return $this;
    }

    /**
     * Get nestTemplate
     *
     * @return \Nononsense\HomeBundle\Entity\TMTemplates 
     */
    public function getNestTemplate()
    {
        return $this->nestTemplate;
    }
}
