<?php

namespace Nononsense\HomeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(
 *      name="mc_cleans",
 *      indexes={
 *          @ORM\Index(columns={"code"}),
 *          @ORM\Index(columns={"lot_number"}),
 *          @ORM\Index(columns={"status"}),
 *      }
 * )
 * @ORM\Entity(repositoryClass="Nononsense\HomeBundle\Entity\MaterialCleanCleansRepository")
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
     * @ORM\ManyToOne(targetEntity="\Nononsense\HomeBundle\Entity\MaterialCleanCenters", inversedBy="cleans")
     * @ORM\JoinColumn(name="id_center", referencedColumnName="id", nullable=false)
     */
    protected $center;

    /**
     * @ORM\ManyToOne(targetEntity="\Nononsense\HomeBundle\Entity\MaterialCleanMaterials", inversedBy="cleans")
     * @ORM\JoinColumn(name="id_material", referencedColumnName="id", nullable=false)
     */
    protected $material;

    /**
     * @ORM\Column(name="material_other", type="text",  nullable=true)
     */
    protected $materialOther;

    /**
     * @ORM\Column(name="additional_info", type="text",  nullable=true)
     */
    protected $additionalInfo;

    /**
     * @ORM\Column(name="code", type="string", length=255,  nullable=false)
     */
    protected $code;

    /**
     * @ORM\Column(name="lot_number", type="string", nullable=true)
     */
    protected $lotNumber;

    /**
     * @var string
     *
     * @ORM\Column(name="clean_signature", type="text", nullable=false)
     */
    protected $signature;

    /**
     * @ORM\Column(name="clean_date", type="datetime",  nullable=false)
     */
    protected $cleanDate;

    /**
     * @ORM\Column(name="clean_expired_date", type="datetime", nullable=false)
     */
    protected $cleanExpiredDate;

    /**
     * @ORM\ManyToOne(targetEntity="\Nononsense\UserBundle\Entity\Users", inversedBy="materialClean")
     * @ORM\JoinColumn(name="clean_user", referencedColumnName="id", nullable=false)
     */
    protected $cleanUser;

    /**
     * @ORM\Column(name="verification_date", type="datetime", nullable=true)
     */
    protected $verificationDate;

    /**
     * @ORM\Column(name="verification_signature", type="text", nullable=true)
     */
    protected $verificationSignature;

    /**
     * @ORM\ManyToOne(targetEntity="\Nononsense\UserBundle\Entity\Users", inversedBy="materialVerification")
     * @ORM\JoinColumn(name="verification_user", referencedColumnName="id", nullable=true)
     */
    protected $verificationUser;

    /**
     * @ORM\Column(name="use_information", type="text",  nullable=true)
     */
    protected $useInformation;

    /**
     * @ORM\Column(name="dirty_material_date", type="datetime", nullable=true)
     */
    protected $dirtyMaterialDate;

    /**
     * @ORM\Column(name="dirty_material_signature", type="text", nullable=true)
     */
    protected $dirtyMaterialSignature;

    /**
     * @ORM\ManyToOne(targetEntity="\Nononsense\UserBundle\Entity\Users", inversedBy="materialDirty")
     * @ORM\JoinColumn(name="dirty_material_user", referencedColumnName="id", nullable=true)
     */
    protected $dirtyMaterialUser;

    /**
     * @ORM\Column(name="review_date", type="datetime", nullable=true)
     */
    protected $reviewDate;

    /**
     * @ORM\Column(name="review_signature", type="text", nullable=true)
     */
    protected $reviewSignature;

    /**
     * @ORM\ManyToOne(targetEntity="\Nononsense\UserBundle\Entity\Users", inversedBy="materialReview")
     * @ORM\JoinColumn(name="review_user", referencedColumnName="id", nullable=true)
     */
    protected $reviewUser;

    /**
     * @ORM\Column(name="review_information", type="text",  nullable=true)
     */
    protected $reviewInformation;


    /**
     * @ORM\Column(name="cancel_date", type="datetime", nullable=true)
     */
    protected $cancelDate;

    /**
     * @ORM\Column(name="cancel_signature", type="text", nullable=true)
     */
    protected $cancelSignature;

    /**
     * @ORM\ManyToOne(targetEntity="\Nononsense\UserBundle\Entity\Users", inversedBy="materialCancel")
     * @ORM\JoinColumn(name="cancel_user", referencedColumnName="id", nullable=true)
     */
    protected $cancelUser;

    /**
     * @ORM\Column(name="cancel_information", type="text",  nullable=true)
     */
    protected $cancelInformation;

    /**
     * @ORM\Column(name="status", type="integer",  nullable=false, options={"default" : 1})
     */
    protected $status;

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
     * Set lotNumber
     *
     * @param string $lotNumber
     * @return MaterialCleanCleans
     */
    public function setLotNumber($lotNumber)
    {
        $this->lotNumber = $lotNumber;

        return $this;
    }

    /**
     * Get lotNumber
     *
     * @return string 
     */
    public function getLotNumber()
    {
        return $this->lotNumber;
    }

    /**
     * Set verificationDate
     *
     * @param \DateTime $verificationDate
     * @return MaterialCleanCleans
     */
    public function setVerificationDate($verificationDate)
    {
        $this->verificationDate = $verificationDate;

        return $this;
    }

    /**
     * Get verificationDate
     *
     * @return \DateTime 
     */
    public function getVerificationDate()
    {
        return $this->verificationDate;
    }

    /**
     * Set verificationSignature
     *
     * @param string $verificationSignature
     * @return MaterialCleanCleans
     */
    public function setVerificationSignature($verificationSignature)
    {
        $this->verificationSignature = $verificationSignature;

        return $this;
    }

    /**
     * Get verificationSignature
     *
     * @return string 
     */
    public function getVerificationSignature()
    {
        return $this->verificationSignature;
    }

    /**
     * Set dirtyMaterialDate
     *
     * @param \DateTime $dirtyMaterialDate
     * @return MaterialCleanCleans
     */
    public function setDirtyMaterialDate($dirtyMaterialDate)
    {
        $this->dirtyMaterialDate = $dirtyMaterialDate;

        return $this;
    }

    /**
     * Get dirtyMaterialDate
     *
     * @return \DateTime 
     */
    public function getDirtyMaterialDate()
    {
        return $this->dirtyMaterialDate;
    }

    /**
     * Set dirtyMaterialSignature
     *
     * @param string $dirtyMaterialSignature
     * @return MaterialCleanCleans
     */
    public function setDirtyMaterialSignature($dirtyMaterialSignature)
    {
        $this->dirtyMaterialSignature = $dirtyMaterialSignature;

        return $this;
    }

    /**
     * Get dirtyMaterialSignature
     *
     * @return string 
     */
    public function getDirtyMaterialSignature()
    {
        return $this->dirtyMaterialSignature;
    }

    /**
     * Set reviewDate
     *
     * @param \DateTime $reviewDate
     * @return MaterialCleanCleans
     */
    public function setReviewDate($reviewDate)
    {
        $this->reviewDate = $reviewDate;

        return $this;
    }

    /**
     * Get reviewDate
     *
     * @return \DateTime 
     */
    public function getReviewDate()
    {
        return $this->reviewDate;
    }

    /**
     * Set reviewSignature
     *
     * @param string $reviewSignature
     * @return MaterialCleanCleans
     */
    public function setReviewSignature($reviewSignature)
    {
        $this->reviewSignature = $reviewSignature;

        return $this;
    }

    /**
     * Get reviewSignature
     *
     * @return string 
     */
    public function getReviewSignature()
    {
        return $this->reviewSignature;
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
     * Set cleanUser
     *
     * @param \Nononsense\UserBundle\Entity\Users $cleanUser
     * @return MaterialCleanCleans
     */
    public function setCleanUser(\Nononsense\UserBundle\Entity\Users $cleanUser)
    {
        $this->cleanUser = $cleanUser;

        return $this;
    }

    /**
     * Get cleanUser
     *
     * @return \Nononsense\UserBundle\Entity\Users 
     */
    public function getCleanUser()
    {
        return $this->cleanUser;
    }

    /**
     * Set verificationUser
     *
     * @param \Nononsense\UserBundle\Entity\Users $verificationUser
     * @return MaterialCleanCleans
     */
    public function setVerificationUser(\Nononsense\UserBundle\Entity\Users $verificationUser = null)
    {
        $this->verificationUser = $verificationUser;

        return $this;
    }

    /**
     * Get verificationUser
     *
     * @return \Nononsense\UserBundle\Entity\Users 
     */
    public function getVerificationUser()
    {
        return $this->verificationUser;
    }

    /**
     * Set dirtyMaterialUser
     *
     * @param \Nononsense\UserBundle\Entity\Users $dirtyMaterialUser
     * @return MaterialCleanCleans
     */
    public function setDirtyMaterialUser(\Nononsense\UserBundle\Entity\Users $dirtyMaterialUser = null)
    {
        $this->dirtyMaterialUser = $dirtyMaterialUser;

        return $this;
    }

    /**
     * Get dirtyMaterialUser
     *
     * @return \Nononsense\UserBundle\Entity\Users 
     */
    public function getDirtyMaterialUser()
    {
        return $this->dirtyMaterialUser;
    }

    /**
     * Set reviewUser
     *
     * @param \Nononsense\UserBundle\Entity\Users $reviewUser
     * @return MaterialCleanCleans
     */
    public function setReviewUser(\Nononsense\UserBundle\Entity\Users $reviewUser = null)
    {
        $this->reviewUser = $reviewUser;

        return $this;
    }

    /**
     * Get reviewUser
     *
     * @return \Nononsense\UserBundle\Entity\Users 
     */
    public function getReviewUser()
    {
        return $this->reviewUser;
    }

    /**
     * Set status
     *
     * @param integer $status
     * @return MaterialCleanCleans
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return integer 
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set useInformation
     *
     * @param string $useInformation
     * @return MaterialCleanCleans
     */
    public function setUseInformation($useInformation)
    {
        $this->useInformation = $useInformation;

        return $this;
    }

    /**
     * Get useInformation
     *
     * @return string 
     */
    public function getUseInformation()
    {
        return $this->useInformation;
    }

    /**
     * Set reviewInformation
     *
     * @param string $reviewInformation
     * @return MaterialCleanCleans
     */
    public function setReviewInformation($reviewInformation)
    {
        $this->reviewInformation = $reviewInformation;

        return $this;
    }

    /**
     * Get reviewInformation
     *
     * @return string 
     */
    public function getReviewInformation()
    {
        return $this->reviewInformation;
    }

    /**
     * Set materialOther
     *
     * @param string $materialOther
     * @return MaterialCleanCleans
     */
    public function setMaterialOther($materialOther)
    {
        $this->materialOther = $materialOther;

        return $this;
    }

    /**
     * Get materialOther
     *
     * @return string 
     */
    public function getMaterialOther()
    {
        return $this->materialOther;
    }

    /**
     * Set additionalInfo
     *
     * @param string $additionalInfo
     * @return MaterialCleanCleans
     */
    public function setAdditionalInfo($additionalInfo)
    {
        $this->additionalInfo = $additionalInfo;

        return $this;
    }

    /**
     * Get additionalInfo
     *
     * @return string 
     */
    public function getAdditionalInfo()
    {
        return $this->additionalInfo;
    }

    


    /**
     * Set cancelDate
     *
     * @param \DateTime $cancelDate
     * @return MaterialCleanCleans
     */
    public function setCancelDate($cancelDate)
    {
        $this->cancelDate = $cancelDate;

        return $this;
    }

    /**
     * Get cancelDate
     *
     * @return \DateTime 
     */
    public function getCancelDate()
    {
        return $this->cancelDate;
    }

    /**
     * Set cancelSignature
     *
     * @param string $cancelSignature
     * @return MaterialCleanCleans
     */
    public function setCancelSignature($cancelSignature)
    {
        $this->cancelSignature = $cancelSignature;

        return $this;
    }

    /**
     * Get cancelSignature
     *
     * @return string 
     */
    public function getCancelSignature()
    {
        return $this->cancelSignature;
    }

    /**
     * Set cancelInformation
     *
     * @param string $cancelInformation
     * @return MaterialCleanCleans
     */
    public function setCancelInformation($cancelInformation)
    {
        $this->cancelInformation = $cancelInformation;

        return $this;
    }

    /**
     * Get cancelInformation
     *
     * @return string 
     */
    public function getCancelInformation()
    {
        return $this->cancelInformation;
    }

    /**
     * Set cancelUser
     *
     * @param \Nononsense\UserBundle\Entity\Users $cancelUser
     * @return MaterialCleanCleans
     */
    public function setCancelUser(\Nononsense\UserBundle\Entity\Users $cancelUser = null)
    {
        $this->cancelUser = $cancelUser;

        return $this;
    }

    /**
     * Get cancelUser
     *
     * @return \Nononsense\UserBundle\Entity\Users 
     */
    public function getCancelUser()
    {
        return $this->cancelUser;
    }
}
