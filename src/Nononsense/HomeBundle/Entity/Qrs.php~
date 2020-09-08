<?php

namespace Nononsense\HomeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity
 * @ORM\Table(name="qrs")
 * @ORM\Entity(repositoryClass="Nononsense\HomeBundle\Entity\QrsRepository")
 * @ORM\HasLifecycleCallbacks
 */
class Qrs
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
     * @ORM\Column(name="name", type="string", length=255,  nullable=true)
     */
    protected $name;

    /**
     * @ORM\OneToMany(targetEntity="\Nononsense\HomeBundle\Entity\QrsFields", mappedBy="qr")
     * @Groups({"group1"})
     */
    protected $fields;

    /**
     * @ORM\ManyToOne(targetEntity="\Nononsense\HomeBundle\Entity\QrsTypes", inversedBy="qrs")
     * @ORM\JoinColumn(name="type_id", referencedColumnName="id")
     * @Groups({"group1"})
     */
    protected $type;


    public function __construct()
    {
        $this->fields = new ArrayCollection();
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
     * @return Qrs
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
     * Add fields
     *
     * @param \Nononsense\HomeBundle\Entity\QrsFields $fields
     * @return Qrs
     */
    public function addField(\Nononsense\HomeBundle\Entity\QrsFields $fields)
    {
        $this->fields[] = $fields;

        return $this;
    }

    /**
     * Remove fields
     *
     * @param \Nononsense\HomeBundle\Entity\QrsFields $fields
     */
    public function removeField(\Nononsense\HomeBundle\Entity\QrsFields $fields)
    {
        $this->fields->removeElement($fields);
    }

    /**
     * Get fields
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getFields()
    {
        return $this->fields;
    }

    /**
     * Set type
     *
     * @param \Nononsense\HomeBundle\Entity\QrsTypes $type
     * @return Qrs
     */
    public function setType(\Nononsense\HomeBundle\Entity\QrsTypes $type = null)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return \Nononsense\HomeBundle\Entity\QrsTypes 
     */
    public function getType()
    {
        return $this->type;
    }
}
