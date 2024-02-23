<?php

namespace Nononsense\HomeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity
 * @ORM\Table(name="qrs_fields")
 * @ORM\Entity(repositoryClass="Nononsense\HomeBundle\Entity\QrsFieldsRepository")
 * @ORM\HasLifecycleCallbacks
 */
class QrsFields
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * @Groups({"group1"})
     */
    protected $id;


    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255,  nullable=true)
     *
     * @Groups({"group1"})
     */
    protected $name;

    /**
     * @var string
     *
     * @ORM\Column(name="value", type="string", length=255,  nullable=true)
     *
     * @Groups({"group1"})
     */
    protected $value;

    /**
     * @ORM\ManyToOne(targetEntity="\Nononsense\HomeBundle\Entity\Qrs", inversedBy="fields")
     * @ORM\JoinColumn(name="qr_id", referencedColumnName="id")
     */
    protected $qr;


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
     * @return QrsFields
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
     * Set qr
     *
     * @param \Nononsense\HomeBundle\Entity\Qrs $qr
     * @return QrsFields
     */
    public function setQr(\Nononsense\HomeBundle\Entity\Qrs $qr = null)
    {
        $this->qr = $qr;

        return $this;
    }

    /**
     * Get qr
     *
     * @return \Nononsense\HomeBundle\Entity\Qrs 
     */
    public function getQr()
    {
        return $this->qr;
    }

    /**
     * Set value
     *
     * @param string $value
     * @return QrsFields
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Get value
     *
     * @return string 
     */
    public function getValue()
    {
        return $this->value;
    }
}
