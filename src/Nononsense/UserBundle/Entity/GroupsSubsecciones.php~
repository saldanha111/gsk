<?php
/**
 * Created by IntelliJ IDEA.
 * User: gushe
 * Date: 26/06/2018
 * Time: 12:55
 */

namespace Nononsense\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="groupssubsecciones")
 * @ORM\Entity(repositoryClass="Nononsense\UserBundle\Entity\GroupsSubseccionesRepository")
 * @ORM\HasLifecycleCallbacks
 */
class GroupsSubsecciones
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="\Nononsense\GroupBundle\Entity\Groups", inversedBy="groupsSubsecciones")
     * @ORM\JoinColumn(name="group_id", referencedColumnName="id")
     */
    protected $group;

    /**
     * @ORM\ManyToOne(targetEntity="\Nononsense\UserBundle\Entity\Subsecciones", inversedBy="groupsSubsecciones")
     * @ORM\JoinColumn(name="subseccion_id", referencedColumnName="id")
     */
    protected $subseccion;

    /**
     * Userssection constructor.
     */
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
     * Set group
     *
     * @param \Nononsense\GroupBundle\Entity\Groups $group
     * @return GroupsSubsecciones
     */
    public function setGroup(\Nononsense\GroupBundle\Entity\Groups $group = null)
    {
        $this->group = $group;

        return $this;
    }

    /**
     * Get group
     *
     * @return \Nononsense\GroupBundle\Entity\Groups 
     */
    public function getGroup()
    {
        return $this->group;
    }

    /**
     * Set subseccion
     *
     * @param \Nononsense\UserBundle\Entity\Subsecciones $subseccion
     * @return GroupsSubsecciones
     */
    public function setSubseccion(\Nononsense\UserBundle\Entity\Subsecciones $subseccion = null)
    {
        $this->subseccion = $subseccion;

        return $this;
    }

    /**
     * Get subseccion
     *
     * @return \Nononsense\UserBundle\Entity\Subsecciones 
     */
    public function getSubseccion()
    {
        return $this->subseccion;
    }
}
