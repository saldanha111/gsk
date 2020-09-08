<?php

namespace Nononsense\HomeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="records_contracts_pin_comite")
 * @ORM\Entity(repositoryClass="Nononsense\HomeBundle\Entity\RecordsContractsPinComiteRepository")
 * @ORM\HasLifecycleCallbacks
 */
class RecordsContractsPinComite
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     * @ORM\Column(name="pin", type="string", length=50)
     */
    protected $pin;


    /**
     * @ORM\ManyToOne(targetEntity="\Nononsense\HomeBundle\Entity\RecordsContracts", inversedBy="pinComite")
     * @ORM\JoinColumn(name="contract", referencedColumnName="id")
     */
    protected $contract;

    /**
     * @ORM\ManyToOne(targetEntity="\Nononsense\UserBundle\Entity\Users", inversedBy="pinComite")
     * @ORM\JoinColumn(name="user", referencedColumnName="id")
     */
    protected $user;


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
     * Set pin
     *
     * @param string $pin
     * @return RecordsContractsPinComite
     */
    public function setPin($pin)
    {
        $this->pin = $pin;

        return $this;
    }

    /**
     * Get pin
     *
     * @return string 
     */
    public function getPin()
    {
        return $this->pin;
    }

    /**
     * Set contract
     *
     * @param \Nononsense\HomeBundle\Entity\RecordsContracts $contract
     * @return RecordsContractsPinComite
     */
    public function setContract(\Nononsense\HomeBundle\Entity\RecordsContracts $contract = null)
    {
        $this->contract = $contract;

        return $this;
    }

    /**
     * Get contract
     *
     * @return \Nononsense\HomeBundle\Entity\RecordsContracts 
     */
    public function getContract()
    {
        return $this->contract;
    }

    /**
     * Set user
     *
     * @param \Nononsense\UserBundle\Entity\Users $user
     * @return RecordsContractsPinComite
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
}
