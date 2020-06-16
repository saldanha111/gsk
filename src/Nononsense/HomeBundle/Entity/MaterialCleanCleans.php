<?php

namespace Nononsense\HomeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="mc_cleans")
 * @ORM\Entity(repositoryClass="Nononsense\HomeBundle\Entity\MaterialCleanCentersRepository")
 * @ORM\HasLifecycleCallbacks
 */
class MaterialCleanCleans
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(name="code", type="string", length=255,  nullable=false)
     */
    protected $code;

    /**
     * @var string
     *
     * @ORM\Column(name="signature", type="text", nullable=false)
     */
    protected $signature;

    /**
     * @ORM\Column(name="clean_date", type="datetime",  nullable=false)
     */
    protected $cleanDate;

    /**
     * @ORM\Column(name="clean_expired_date", type="datetime",  nullable=false)
     */
    protected $cleanExpiredDate;

    /**
     * @ORM\ManyToOne(targetEntity="\Nononsense\HomeBundle\Entity\MaterialCleanCenters", inversedBy="cleans")
     * @ORM\JoinColumn(name="id_center", referencedColumnName="id")
     */
    private $center;

    /**
     * @ORM\ManyToOne(targetEntity="\Nononsense\HomeBundle\Entity\MaterialCleanMaterials", inversedBy="cleans")
     * @ORM\JoinColumn(name="id_material", referencedColumnName="id")
     */
    private $material;

    /**
     * @ORM\ManyToOne(targetEntity="\Nononsense\UserBundle\Entity\Users", inversedBy="materialClean")
     * @ORM\JoinColumn(name="id_user", referencedColumnName="id")
     */
    private $user;

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
     * Set code
     *
     * @param string $code
     * @return MaterialCleanCleans
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * Get code
     *
     * @return string 
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Set signature
     *
     * @param string $signature
     * @return MaterialCleanCleans
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
     * Set cleanDate
     *
     * @param \DateTime $cleanDate
     * @return MaterialCleanCleans
     */
    public function setCleanDate($cleanDate)
    {
        $this->cleanDate = $cleanDate;

        return $this;
    }

    /**
     * Get cleanDate
     *
     * @return \DateTime 
     */
    public function getCleanDate()
    {
        return $this->cleanDate;
    }

    /**
     * Set cleanExpiredDate
     *
     * @param \DateTime $cleanExpiredDate
     * @return MaterialCleanCleans
     */
    public function setCleanExpiredDate($cleanExpiredDate)
    {
        $this->cleanExpiredDate = $cleanExpiredDate;

        return $this;
    }

    /**
     * Get cleanExpiredDate
     *
     * @return \DateTime 
     */
    public function getCleanExpiredDate()
    {
        return $this->cleanExpiredDate;
    }

    /**
     * Set center
     *
     * @param \Nononsense\HomeBundle\Entity\MaterialCleanCenters $center
     * @return MaterialCleanCleans
     */
    public function setCenter(\Nononsense\HomeBundle\Entity\MaterialCleanCenters $center = null)
    {
        $this->center = $center;

        return $this;
    }

    /**
     * Get center
     *
     * @return \Nononsense\HomeBundle\Entity\MaterialCleanCenters 
     */
    public function getCenter()
    {
        return $this->center;
    }

    /**
     * Set material
     *
     * @param \Nononsense\HomeBundle\Entity\MaterialCleanMaterials $material
     * @return MaterialCleanCleans
     */
    public function setMaterial(\Nononsense\HomeBundle\Entity\MaterialCleanMaterials $material = null)
    {
        $this->material = $material;

        return $this;
    }

    /**
     * Get material
     *
     * @return \Nononsense\HomeBundle\Entity\MaterialCleanMaterials 
     */
    public function getMaterial()
    {
        return $this->material;
    }

    /**
     * Set user
     *
     * @param \Nononsense\UserBundle\Entity\Users $user
     * @return MaterialCleanCleans
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
}
