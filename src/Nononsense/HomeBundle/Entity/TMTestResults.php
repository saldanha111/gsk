<?php

namespace Nononsense\HomeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity
 * @ORM\Table(name="tm_test_results")
 * @ORM\Entity(repositoryClass="Nononsense\HomeBundle\Entity\TMTestResultsRepository")
 * @ORM\HasLifecycleCallbacks
 */
class TMTestResults
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
     * @ORM\OneToMany(targetEntity="\Nononsense\HomeBundle\Entity\TMTests", mappedBy="result")
     */
    protected $tmTests;



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
     * Add tmTests
     *
     * @param \Nononsense\HomeBundle\Entity\TMTests $tmTests
     * @return TMTestResults
     */
    public function addTmTest(\Nononsense\HomeBundle\Entity\TMTests $tmTests)
    {
        $this->tmTests[] = $tmTests;

        return $this;
    }

    /**
     * Remove tmTests
     *
     * @param \Nononsense\HomeBundle\Entity\TMTests $tmTests
     */
    public function removeTmTest(\Nononsense\HomeBundle\Entity\TMTests $tmTests)
    {
        $this->tmTests->removeElement($tmTests);
    }

    /**
     * Get tmTests
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getTmTests()
    {
        return $this->tmTests;
    }

    /**
     * Set description
     *
     * @param string $description
     * @return TMTestResults
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
