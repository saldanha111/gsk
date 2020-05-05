<?php
/**
 * Created by IntelliJ IDEA.
 * User: gushe
 * Date: 26/06/2018
 * Time: 13:03
 */

namespace Nononsense\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity
 * @ORM\Table(name="secciones")
 * @ORM\Entity(repositoryClass="Nononsense\UserBundle\Entity\SeccionesRepository")
 * @ORM\HasLifecycleCallbacks
 */
class Secciones
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(name="name", type="string", length=90)
     */
    protected $name;

    /**
     * @ORM\OneToMany(targetEntity="\Nononsense\UserBundle\Entity\Subsecciones", mappedBy="seccion")
     */
    protected $subsecciones;

    /**
     * Sections constructor.
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
     * Set name
     *
     * @param string $name
     * @return Secciones
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
     * Add subsecciones
     *
     * @param \Nononsense\UserBundle\Entity\Subsecciones $subsecciones
     * @return Secciones
     */
    public function addSubseccione(\Nononsense\UserBundle\Entity\Subsecciones $subsecciones)
    {
        $this->subsecciones[] = $subsecciones;

        return $this;
    }

    /**
     * Remove subsecciones
     *
     * @param \Nononsense\UserBundle\Entity\Subsecciones $subsecciones
     */
    public function removeSubseccione(\Nononsense\UserBundle\Entity\Subsecciones $subsecciones)
    {
        $this->subsecciones->removeElement($subsecciones);
    }

    /**
     * Get subsecciones
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getSubsecciones()
    {
        return $this->subsecciones;
    }
}
