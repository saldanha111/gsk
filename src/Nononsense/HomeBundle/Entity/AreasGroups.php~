<?php

namespace Nononsense\HomeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * AreasGroups
 *
 * @ORM\Entity
 * @ORM\Table(name="areas_groups")
 * @ORM\Entity(repositoryClass="Nononsense\HomeBundle\Entity\AreasGroupsRepository")
 * @ORM\HasLifecycleCallbacks
 * @UniqueEntity(fields = {"agroup", "area"})
 * 
 */
class AreasGroups
{

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    
    /**
     * @ORM\ManyToOne(targetEntity="\Nononsense\GroupBundle\Entity\Groups", inversedBy="areas")
     * @ORM\JoinColumn(name="group_id", referencedColumnName="id")
     */
    protected $agroup;
    
    
    /**
     * @ORM\ManyToOne(targetEntity="\Nononsense\HomeBundle\Entity\Areas", inversedBy="groups")
     * @ORM\JoinColumn(name="area_id", referencedColumnName="id")
     */
    protected $area;

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
     * Set agroup
     *
     * @param \Nononsense\GroupBundle\Entity\Groups $agroup
     * @return AreasGroups
     */
    public function setAgroup(\Nononsense\GroupBundle\Entity\Groups $agroup = null)
    {
        $this->agroup = $agroup;

        return $this;
    }

    /**
     * Get agroup
     *
     * @return \Nononsense\GroupBundle\Entity\Groups 
     */
    public function getAgroup()
    {
        return $this->agroup;
    }

    /**
     * Set area
     *
     * @param \Nononsense\HomeBundle\Entity\Areas $area
     * @return AreasGroups
     */
    public function setArea(\Nononsense\HomeBundle\Entity\Areas $area = null)
    {
        $this->area = $area;

        return $this;
    }

    /**
     * Get area
     *
     * @return \Nononsense\HomeBundle\Entity\Areas 
     */
    public function getArea()
    {
        return $this->area;
    }
}
