<?php
namespace Nononsense\HomeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity
 * @ORM\Table(name="areas")
 * @ORM\Entity(repositoryClass="Nononsense\HomeBundle\Entity\AreasRepository")
 * @ORM\HasLifecycleCallbacks
 */
class Areas
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({"detail_area","list_area","json"})
     */
    protected $id;

    /**
     * @ORM\OneToMany(targetEntity="\Nononsense\HomeBundle\Entity\AreasSignatures", mappedBy="area")
     */
    protected $areasSignatures;

    /**
     * @ORM\ManyToOne(targetEntity="\Nononsense\HomeBundle\Entity\TMTemplates", inversedBy="areas")
     * @ORM\JoinColumn(name="template_id", referencedColumnName="id")
     * @Groups({"detail_area","list_area"})
     */
    protected $template;

    /**
     * @ORM\OneToMany(targetEntity="\Nononsense\HomeBundle\Entity\AreasGroups", mappedBy="area")
     */
    protected $groups;

    /**
     * @ORM\ManyToOne(targetEntity="\Nononsense\UserBundle\Entity\Users", inversedBy="areas")
     * @ORM\JoinColumn(name="fll_user_id", referencedColumnName="id", nullable=true)
     * @Groups({"detail_area","list_area"})
     */
    protected $fll;

    /**
     * @ORM\OneToMany(targetEntity="\Nononsense\HomeBundle\Entity\AreaPrefixes", mappedBy="area")
     */
    protected $prefixes;
    
    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=200)
     * @Assert\NotBlank(message = "You shoud insert a name")
     * @Groups({"detail_area","list_area"})
     */
    protected $name;

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_active", type="boolean",  nullable=true, options={"default" = false})
     * @Groups({"detail_area","list_area"})
     */
    protected $isActive;

    /**
     * @ORM\Column(type="date")
     */
    protected $created;

    /**
     * @ORM\OneToMany(targetEntity="\Nononsense\HomeBundle\Entity\TMTemplates", mappedBy="area")
     * @Groups({"detail_area","list_area"})
     */
    protected $tmTemplates;



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
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return Master_Workflows
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName()
    {
        return $this->name;
    }


    /**
     * Set isActive
     *
     * @param boolean $isActive
     * @return Master_Workflows
     */
    public function setIsActive($isActive)
    {
        $this->isActive = $isActive;

        return $this;
    }

    /**
     * Get isActive
     *
     * @return boolean 
     */
    public function getIsActive()
    {
        return $this->isActive;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     * @return Master_Workflows
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
     * Get mtTemplates
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getMtTemplates()
    {
        return $this->mtTemplates;
    }

    /**
     * Add tmTemplates
     *
     * @param \Nononsense\HomeBundle\Entity\TMTemplates $tmTemplates
     * @return Areas
     */
    public function addTmTemplate(\Nononsense\HomeBundle\Entity\TMTemplates $tmTemplates)
    {
        $this->tmTemplates[] = $tmTemplates;

        return $this;
    }

    /**
     * Remove tmTemplates
     *
     * @param \Nononsense\HomeBundle\Entity\TMTemplates $tmTemplates
     */
    public function removeTmTemplate(\Nononsense\HomeBundle\Entity\TMTemplates $tmTemplates)
    {
        $this->tmTemplates->removeElement($tmTemplates);
    }

    /**
     * Get tmTemplates
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getTmTemplates()
    {
        return $this->tmTemplates;
    }

    /**
     * Add prefixes
     *
     * @param \Nononsense\HomeBundle\Entity\AreaPrefixes $prefixes
     * @return Areas
     */
    public function addPrefix(\Nononsense\HomeBundle\Entity\AreaPrefixes $prefixes)
    {
        $this->prefixes[] = $prefixes;

        return $this;
    }

    /**
     * Remove prefixes
     *
     * @param \Nononsense\HomeBundle\Entity\AreaPrefixes $prefixes
     */
    public function removePrefix(\Nononsense\HomeBundle\Entity\AreaPrefixes $prefixes)
    {
        $this->prefixes->removeElement($prefixes);
    }

    /**
     * Get prefixes
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getPrefixes()
    {
        return $this->prefixes;
    }

    /**
     * Add groups
     *
     * @param \Nononsense\HomeBundle\Entity\AreasGroups $groups
     * @return Areas
     */
    public function addGroup(\Nononsense\HomeBundle\Entity\AreasGroups $groups)
    {
        $this->groups[] = $groups;

        return $this;
    }

    /**
     * Remove groups
     *
     * @param \Nononsense\HomeBundle\Entity\AreasGroups $groups
     */
    public function removeGroup(\Nononsense\HomeBundle\Entity\AreasGroups $groups)
    {
        $this->groups->removeElement($groups);
    }

    /**
     * Get groups
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getGroups()
    {
        return $this->groups;
    }

    /**
     * Set template
     *
     * @param \Nononsense\HomeBundle\Entity\TMTemplates $template
     * @return Areas
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
     * Set fll
     *
     * @param \Nononsense\UserBundle\Entity\Users $fll
     * @return Areas
     */
    public function setFll(\Nononsense\UserBundle\Entity\Users $fll = null)
    {
        $this->fll = $fll;

        return $this;
    }

    /**
     * Get fll
     *
     * @return \Nononsense\UserBundle\Entity\Users 
     */
    public function getFll()
    {
        return $this->fll;
    }


    /**
     * Add areasSignatures
     *
     * @param \Nononsense\HomeBundle\Entity\AreasSignatures $areasSignatures
     * @return Areas
     */
    public function addAreasSignature(\Nononsense\HomeBundle\Entity\AreasSignatures $areasSignatures)
    {
        $this->areasSignatures[] = $areasSignatures;

        return $this;
    }

    /**
     * Remove areasSignatures
     *
     * @param \Nononsense\HomeBundle\Entity\AreasSignatures $areasSignatures
     */
    public function removeAreasSignature(\Nononsense\HomeBundle\Entity\AreasSignatures $areasSignatures)
    {
        $this->areasSignatures->removeElement($areasSignatures);
    }

    /**
     * Get areasSignatures
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getAreasSignatures()
    {
        return $this->areasSignatures;
    }
}
