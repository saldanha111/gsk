<?php

namespace Nononsense\HomeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * InstanciasStepsHistory
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Nononsense\HomeBundle\Entity\InstanciasStepsHistoryRepository")
 */
class InstanciasStepsHistory
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
     * @ORM\Column(name="field", type="string", length=30)
     */
    private $field;

    /**
     * @var string
     *
     * @ORM\Column(name="field_index", type="string", length=30, nullable=true)
     */
    private $index;

    /**
     * @var string
     *
     * @ORM\Column(name="field_value", type="text")
     */
    private $value;

    /**
     * @var string
     *
     * @ORM\Column(name="prev_value", type="text", nullable=true)
     */
    private $prevValue;

    /**
     * @var string
     *
     * @ORM\Column(name="line_options", type="boolean", nullable=true)
     */
    private $lineOptions;

    /**
     * @ORM\ManyToOne(targetEntity="EvidenciasStep")
     * @ORM\JoinColumn(name="evidencia_id", referencedColumnName="id")
     */
    private $evidencia;


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
     * Set field
     *
     * @param string $field
     * @return InstanciasStepsHistory
     */
    public function setField($field)
    {
        $this->field = $field;

        return $this;
    }

    /**
     * Get field
     *
     * @return string 
     */
    public function getField()
    {
        return $this->field;
    }

    /**
     * Set value
     *
     * @param string $value
     * @return InstanciasStepsHistory
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

    /**
     * Set prevValue
     *
     * @param string $prevValue
     * @return InstanciasStepsHistory
     */
    public function setPrevValue($prevValue)
    {
        $this->prevValue = $prevValue;

        return $this;
    }

    /**
     * Get prevValue
     *
     * @return string 
     */
    public function getPrevValue()
    {
        return $this->prevValue;
    }

    /**
     * Set prevValue
     *
     * @param string $index
     * @return InstanciasStepsHistory
     */
    public function setIndex($index)
    {
        $this->index = $index;

        return $this;
    }

    /**
     * Get index
     *
     * @return string 
     */
    public function getIndex()
    {
        return $this->index;
    }

    /**
     * Set evidencia
     *
     * @param \Nononsense\HomeBundle\Entity\EvidenciasStep $evidencia
     * @return InstanciasStepsHistory
     */
    public function setEvidencia(\Nononsense\HomeBundle\Entity\EvidenciasStep $evidencia = null)
    {
        $this->evidencia = $evidencia;

        return $this;
    }

    /**
     * Get evidencia
     *
     * @return \Nononsense\HomeBundle\Entity\EvidenciasStep 
     */
    public function getEvidencia()
    {
        return $this->evidencia;
    }

    /**
     * Set lineOptions
     *
     * @param boolean $lineOptions
     * @return InstanciasStepsHistory
     */
    public function setLineOptions($lineOptions)
    {
        $this->lineOptions = $lineOptions;

        return $this;
    }

    /**
     * Get lineOptions
     *
     * @return boolean 
     */
    public function getLineOptions()
    {
        return $this->lineOptions;
    }
}
