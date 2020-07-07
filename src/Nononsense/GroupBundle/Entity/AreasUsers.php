<?php

namespace Nononsense\GroupBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * AreasUsers
 */
class AreasUsers
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var \Nononsense\UserBundle\Entity\Users
     */
    private $user;

    /**
     * @var \Nononsense\HomeBundle\Entity\Areas
     */
    private $area;


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
     * Set user
     *
     * @param \Nononsense\UserBundle\Entity\Users $user
     * @return AreasUsers
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

    /**
     * Set area
     *
     * @param \Nononsense\HomeBundle\Entity\Areas $area
     * @return AreasUsers
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
