<?php

namespace Nononsense\HomeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CertificationsType
 *
 * @ORM\Table(name="certifications_type")
 * @ORM\Entity
 */
class CertificationsType
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
    * @ORM\OneToMany(targetEntity="Certifications", mappedBy="type", cascade={"persist", "remove"})
    */
    private $certifications;

    /**
     * @ORM\ManyToOne(targetEntity=CertificationsType::class, inversedBy="children")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id")
     */
    private $parent;

    /**
     * @ORM\OneToMany(targetEntity=CertificationsType::class, mappedBy="parent")
     */
    private $children;

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
     * @return CertificationsType
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
     * Constructor
     */
    public function __construct()
    {
        $this->certifications = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add certifications
     *
     * @param \Nononsense\HomeBundle\Entity\Certifications $certifications
     * @return CertificationsType
     */
    public function addCertification(\Nononsense\HomeBundle\Entity\Certifications $certifications)
    {
        $this->certifications[] = $certifications;

        return $this;
    }

    /**
     * Remove certifications
     *
     * @param \Nononsense\HomeBundle\Entity\Certifications $certifications
     */
    public function removeCertification(\Nononsense\HomeBundle\Entity\Certifications $certifications)
    {
        $this->certifications->removeElement($certifications);
    }

    /**
     * Get certifications
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getCertifications()
    {
        return $this->certifications;
    }

    /**
     * Set parent
     *
     * @param \Nononsense\HomeBundle\Entity\CertificationsType $parent
     * @return CertificationsType
     */
    public function setParent(\Nononsense\HomeBundle\Entity\CertificationsType $parent = null)
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * Get parent
     *
     * @return \Nononsense\HomeBundle\Entity\CertificationsType 
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Add children
     *
     * @param \Nononsense\HomeBundle\Entity\CertificationsType $children
     * @return CertificationsType
     */
    public function addChild(\Nononsense\HomeBundle\Entity\CertificationsType $children)
    {
        $this->children[] = $children;

        return $this;
    }

    /**
     * Remove children
     *
     * @param \Nononsense\HomeBundle\Entity\CertificationsType $children
     */
    public function removeChild(\Nononsense\HomeBundle\Entity\CertificationsType $children)
    {
        $this->children->removeElement($children);
    }

    /**
     * Get children
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getChildren()
    {
        return $this->children;
    }
}
