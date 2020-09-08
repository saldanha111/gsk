<?php

namespace Nononsense\HomeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="mc_codes")
 * @ORM\Entity(repositoryClass="Nononsense\HomeBundle\Entity\MaterialCleanCodesRepository")
 * @ORM\HasLifecycleCallbacks
 */
class MaterialCleanCodes
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="\Nononsense\HomeBundle\Entity\MaterialCleanCenters", inversedBy="barcode")
     * @ORM\JoinColumn(name="id_center", referencedColumnName="id")
     */
    private $idCenter;

    /**
     * @ORM\ManyToOne(targetEntity="\Nononsense\HomeBundle\Entity\MaterialCleanMaterials", inversedBy="barcode")
     * @ORM\JoinColumn(name="id_material", referencedColumnName="id")
     */
    private $idMaterial;

    /**
     * @var string
     *
     * @ORM\Column(name="code", type="string", length=255, nullable=true)
     */
    protected $code;

    /**
     * MaterialCleanCodes constructor.
     * @param string $code
     */
    public function __construct(string $code = '')
    {
        $this->setCode($code);
        $this->setIdMaterial(new MaterialCleanMaterials());
        $this->setIdCenter(new MaterialCleanCenters());
    }


    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set idCenter
     *
     * @param MaterialCleanCenters $idCenter
     * @return MaterialCleanCodes
     */
    public function setIdCenter($idCenter)
    {
        $this->idCenter = $idCenter;

        return $this;
    }

    /**
     * Get idCenter
     *
     * @return MaterialCleanCenters
     */
    public function getIdCenter()
    {
        return $this->idCenter;
    }

    /**
     * Set idMaterial
     *
     * @param MaterialCleanMaterials $idMaterial
     * @return MaterialCleanCodes
     */
    public function setIdMaterial($idMaterial)
    {
        $this->idMaterial = $idMaterial;

        return $this;
    }

    /**
     * Get idMaterial
     *
     * @return MaterialCleanMaterials
     */
    public function getIdMaterial()
    {
        return $this->idMaterial;
    }

    /**
     * Set code
     *
     * @param string $code
     * @return MaterialCleanCodes
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
}
