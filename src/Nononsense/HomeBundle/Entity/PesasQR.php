<?php
/**
 * Created by IntelliJ IDEA.
 * User: gushe
 * Date: 23/09/2019
 * Time: 8:42
 */

namespace Nononsense\HomeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity
 * @ORM\Table(name="pesasqr")
 * @ORM\Entity(repositoryClass="Nononsense\HomeBundle\Entity\PesasQRRepository")
 * @ORM\HasLifecycleCallbacks
 */
class PesasQR
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
     * @ORM\Column(name="equipo", type="string", length=100)
     */
    protected $equipo;

    /**
     * @var string
     *
     * @ORM\Column(name="sap", type="string", length=100)
     */
    protected $sap;

    /**
     * @var string
     *
     * @ORM\Column(name="ubicacion", type="string", length=100)
     */
    protected $ubicacion;

    /**
     * @var string
     *
     * @ORM\Column(name="decimales", type="string", length=100)
     */
    protected $decimales;

    /**
     * @var string
     *
     * @ORM\Column(name="legibilidad", type="string", length=100)
     */
    protected $legibilidad;

    /**
     * @var string
     *
     * @ORM\Column(name="pesadaMaxima", type="string", length=100)
     */
    protected $pesadaMaxima;

    /**
     * @var string
     *
     * @ORM\Column(name="pesadaMinima", type="string", length=100)
     */
    protected $pesadaMinima;

    /**
     * @var string
     *
     * @ORM\Column(name="pesaChequeoSensibilidad", type="string", length=100)
     */
    protected $pesaChequeoSensibilidad;

    /**
     * @var string
     *
     * @ORM\Column(name="CL", type="string", length=100)
     */
    protected $CL;

    /**
     * @var string
     *
     * @ORM\Column(name="CL_inf", type="string", length=100)
     */
    protected $CL_inf;

    /**
     * @var string
     *
     * @ORM\Column(name="CL_sup", type="string", length=100)
     */
    protected $CL_sup;

    /**
     * @var string
     *
     * @ORM\Column(name="WL", type="string", length=100)
     */
    protected $WL;

    /**
     * @var string
     *
     * @ORM\Column(name="WL_sup", type="string", length=100)
     */
    protected $WL_sup;

    /**
     * @var string
     *
     * @ORM\Column(name="WL_inf", type="string", length=100)
     */
    protected $WL_inf;

    /**
     * @var string
     *
     * @ORM\Column(name="PesaChequeoRepetibilidad", type="string", length=100)
     */
    protected $PesaChequeoRepetibilidad;

    /**
     * @var string
     *
     * @ORM\Column(name="CLDev", type="string", length=100)
     */
    protected $CLDev;

    /**
     * @var string
     *
     * @ORM\Column(name="WLDev", type="string", length=100)
     */
    protected $WLDev;




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
     * Set equipo
     *
     * @param string $equipo
     * @return PesasQR
     */
    public function setEquipo($equipo)
    {
        $this->equipo = $equipo;

        return $this;
    }

    /**
     * Get equipo
     *
     * @return string 
     */
    public function getEquipo()
    {
        return $this->equipo;
    }

    /**
     * Set sap
     *
     * @param string $sap
     * @return PesasQR
     */
    public function setSap($sap)
    {
        $this->sap = $sap;

        return $this;
    }

    /**
     * Get sap
     *
     * @return string 
     */
    public function getSap()
    {
        return $this->sap;
    }

    /**
     * Set ubicacion
     *
     * @param string $ubicacion
     * @return PesasQR
     */
    public function setUbicacion($ubicacion)
    {
        $this->ubicacion = $ubicacion;

        return $this;
    }

    /**
     * Get ubicacion
     *
     * @return string 
     */
    public function getUbicacion()
    {
        return $this->ubicacion;
    }

    /**
     * Set decimales
     *
     * @param string $decimales
     * @return PesasQR
     */
    public function setDecimales($decimales)
    {
        $this->decimales = $decimales;

        return $this;
    }

    /**
     * Get decimales
     *
     * @return string 
     */
    public function getDecimales()
    {
        return $this->decimales;
    }

    /**
     * Set legibilidad
     *
     * @param string $legibilidad
     * @return PesasQR
     */
    public function setLegibilidad($legibilidad)
    {
        $this->legibilidad = $legibilidad;

        return $this;
    }

    /**
     * Get legibilidad
     *
     * @return string 
     */
    public function getLegibilidad()
    {
        return $this->legibilidad;
    }

    /**
     * Set pesadaMaxima
     *
     * @param string $pesadaMaxima
     * @return PesasQR
     */
    public function setPesadaMaxima($pesadaMaxima)
    {
        $this->pesadaMaxima = $pesadaMaxima;

        return $this;
    }

    /**
     * Get pesadaMaxima
     *
     * @return string 
     */
    public function getPesadaMaxima()
    {
        return $this->pesadaMaxima;
    }

    /**
     * Set pesadaMinima
     *
     * @param string $pesadaMinima
     * @return PesasQR
     */
    public function setPesadaMinima($pesadaMinima)
    {
        $this->pesadaMinima = $pesadaMinima;

        return $this;
    }

    /**
     * Get pesadaMinima
     *
     * @return string 
     */
    public function getPesadaMinima()
    {
        return $this->pesadaMinima;
    }

    /**
     * Set pesaChequeoSensibilidad
     *
     * @param string $pesaChequeoSensibilidad
     * @return PesasQR
     */
    public function setPesaChequeoSensibilidad($pesaChequeoSensibilidad)
    {
        $this->pesaChequeoSensibilidad = $pesaChequeoSensibilidad;

        return $this;
    }

    /**
     * Get pesaChequeoSensibilidad
     *
     * @return string 
     */
    public function getPesaChequeoSensibilidad()
    {
        return $this->pesaChequeoSensibilidad;
    }

    /**
     * Set CL
     *
     * @param string $cL
     * @return PesasQR
     */
    public function setCL($cL)
    {
        $this->CL = $cL;

        return $this;
    }

    /**
     * Get CL
     *
     * @return string 
     */
    public function getCL()
    {
        return $this->CL;
    }

    /**
     * Set CL_inf
     *
     * @param string $cLInf
     * @return PesasQR
     */
    public function setCLInf($cLInf)
    {
        $this->CL_inf = $cLInf;

        return $this;
    }

    /**
     * Get CL_inf
     *
     * @return string 
     */
    public function getCLInf()
    {
        return $this->CL_inf;
    }

    /**
     * Set CL_sup
     *
     * @param string $cLSup
     * @return PesasQR
     */
    public function setCLSup($cLSup)
    {
        $this->CL_sup = $cLSup;

        return $this;
    }

    /**
     * Get CL_sup
     *
     * @return string 
     */
    public function getCLSup()
    {
        return $this->CL_sup;
    }

    /**
     * Set WL_sup
     *
     * @param string $wLSup
     * @return PesasQR
     */
    public function setWLSup($wLSup)
    {
        $this->WL_sup = $wLSup;

        return $this;
    }

    /**
     * Get WL_sup
     *
     * @return string 
     */
    public function getWLSup()
    {
        return $this->WL_sup;
    }

    /**
     * Set WL
     *
     * @param string $wL
     * @return PesasQR
     */
    public function setWL($wL)
    {
        $this->WL = $wL;

        return $this;
    }

    /**
     * Get WL
     *
     * @return string 
     */
    public function getWL()
    {
        return $this->WL;
    }

    /**
     * Set PesaChequeoRepetibilidad
     *
     * @param string $pesaChequeoRepetibilidad
     * @return PesasQR
     */
    public function setPesaChequeoRepetibilidad($pesaChequeoRepetibilidad)
    {
        $this->PesaChequeoRepetibilidad = $pesaChequeoRepetibilidad;

        return $this;
    }

    /**
     * Get PesaChequeoRepetibilidad
     *
     * @return string 
     */
    public function getPesaChequeoRepetibilidad()
    {
        return $this->PesaChequeoRepetibilidad;
    }

    /**
     * Set CLDev
     *
     * @param string $cLDev
     * @return PesasQR
     */
    public function setCLDev($cLDev)
    {
        $this->CLDev = $cLDev;

        return $this;
    }

    /**
     * Get CLDev
     *
     * @return string 
     */
    public function getCLDev()
    {
        return $this->CLDev;
    }

    /**
     * Set WLDev
     *
     * @param string $wLDev
     * @return PesasQR
     */
    public function setWLDev($wLDev)
    {
        $this->WLDev = $wLDev;

        return $this;
    }

    /**
     * Get WLDev
     *
     * @return string 
     */
    public function getWLDev()
    {
        return $this->WLDev;
    }

    /**
     * Set WL_inf
     *
     * @param string $wLInf
     * @return PesasQR
     */
    public function setWLInf($wLInf)
    {
        $this->WL_inf = $wLInf;

        return $this;
    }

    /**
     * Get WL_inf
     *
     * @return string 
     */
    public function getWLInf()
    {
        return $this->WL_inf;
    }
}
