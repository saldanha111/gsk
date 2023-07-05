<?php

namespace Nononsense\HomeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity
 * @ORM\Table(name="tm_test_aprob")
 * @ORM\Entity(repositoryClass="Nononsense\HomeBundle\Entity\TMTestAprobRepository")
 * @ORM\HasLifecycleCallbacks
 */
class TMTestAprob
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({"group1"})
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255,  nullable=true)
     */
    protected $name;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    protected $description;

    /**
     * @ORM\OneToMany(targetEntity="\Nononsense\HomeBundle\Entity\TMSignatures", mappedBy="tmAprobAction")
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
     * Set name
     *
     * @param string $name
     * @return TMStates
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
     * Add tmSignatures
     *
     * @param \Nononsense\HomeBundle\Entity\TMSignatures $tmSignatures
     * @return TMTestAprob
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

    /**
     * Set description
     *
     * @param string $description
     * @return TMTestAprob
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
}
