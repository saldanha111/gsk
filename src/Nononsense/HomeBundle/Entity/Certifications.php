<?php

namespace Nononsense\HomeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Certifications
 *
 * @ORM\Table(name="certifications")
 * @ORM\Entity(repositoryClass="Nononsense\HomeBundle\Entity\CertificationsRepository")
 */
class Certifications
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
     * @ORM\Column(name="tx_hash", type="string", length=255, nullable=true)
     */
    private $txHash;

    /**
     * @var string
     *
     * @ORM\Column(name="hash", type="string", length=255)
     */
    private $hash;

    /**
     * @var integer
     *
     * @ORM\ManyToOne(targetEntity="CertificationsType", inversedBy="certifications")
     * @ORM\JoinColumn(name="type", referencedColumnName="id")
     */
    private $type;

    /**
     * @var integer
     *
     * @ORM\Column(name="record_id", type="integer")
     */
    private $recordId;

    /**
     * @var string
     *
     * @ORM\Column(name="path", type="string", length=255)
     */
    private $path;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $modified;


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
     * Set txHash
     *
     * @param string $txHash
     * @return Certifications
     */
    public function setTxHash($txHash)
    {
        $this->txHash = $txHash;

        return $this;
    }

    /**
     * Get txHash
     *
     * @return string 
     */
    public function getTxHash()
    {
        return $this->txHash;
    }

    /**
     * Set hash
     *
     * @param string $hash
     * @return Certifications
     */
    public function setHash($hash)
    {
        $this->hash = $hash;

        return $this;
    }

    /**
     * Get hash
     *
     * @return string 
     */
    public function getHash()
    {
        return $this->hash;
    }

    /**
     * Set recordId
     *
     * @param integer $recordId
     * @return Certifications
     */
    public function setRecordId($recordId)
    {
        $this->recordId = $recordId;

        return $this;
    }

    /**
     * Get recordId
     *
     * @return integer 
     */
    public function getRecordId()
    {
        return $this->recordId;
    }

    /**
     * Set type
     *
     * @param \Nononsense\HomeBundle\Entity\CertificationsType $type
     * @return Certifications
     */
    public function setType(\Nononsense\HomeBundle\Entity\CertificationsType $type = null)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return \Nononsense\HomeBundle\Entity\CertificationsType 
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set path
     *
     * @param string $path
     * @return Certifications
     */
    public function setPath($path)
    {
        $this->path = $path;

        return $this;
    }

    /**
     * Get path
     *
     * @return string 
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Set modified
     *
     * @param \DateTime $modified
     * @return Certifications
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
}
