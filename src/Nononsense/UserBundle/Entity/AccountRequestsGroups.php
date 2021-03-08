<?php

namespace Nononsense\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * AccountRequestsGroups
 *
 * @ORM\Table(name="account_requests_groups")
 * @ORM\Entity(repositoryClass="Nononsense\UserBundle\Entity\AccountRequestsGroupsRepository")
 */
class AccountRequestsGroups
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
     * @ORM\ManyToOne(targetEntity="AccountRequests", inversedBy="request")
     * @ORM\JoinColumn(name="request_id", referencedColumnName="id")
     */
    private $requestId;

    /**
     * @ORM\ManyToOne(targetEntity="\Nononsense\GroupBundle\Entity\Groups")
     * @ORM\JoinColumn(name="group_id", referencedColumnName="id")
     */
    private $groupId;

    /**
     * @var string
     *
     * @ORM\Column(name="observation", type="text", nullable=true)
     */
    private $observation;

    /**
     * @var boolean
     *
     * @ORM\Column(name="status", type="boolean", nullable=true)
     * @Assert\Regex(
     *     pattern     = "/^([0-1])$/",
     *     message     = "Error al procesar el estado."
     * )
     */
    private $status;


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
     * Set observation
     *
     * @param string $observation
     * @return AccountRequestsGroups
     */
    public function setObservation($observation)
    {
        $this->observation = $observation;

        return $this;
    }

    /**
     * Get observation
     *
     * @return string 
     */
    public function getObservation()
    {
        return $this->observation;
    }

    /**
     * Set requestId
     *
     * @param \Nononsense\UserBundle\Entity\AccountRequests $requestId
     * @return AccountRequestsGroups
     */
    public function setRequestId(\Nononsense\UserBundle\Entity\AccountRequests $requestId = null)
    {
        $this->requestId = $requestId;

        return $this;
    }

    /**
     * Get requestId
     *
     * @return \Nononsense\UserBundle\Entity\AccountRequests 
     */
    public function getRequestId()
    {
        return $this->requestId;
    }

    /**
     * Set groupId
     *
     * @param \Nononsense\GroupBundle\Entity\Groups $groupId
     * @return AccountRequestsGroups
     */
    public function setGroupId(\Nononsense\GroupBundle\Entity\Groups $groupId = null)
    {
        $this->groupId = $groupId;

        return $this;
    }

    /**
     * Get groupId
     *
     * @return \Nononsense\GroupBundle\Entity\Groups 
     */
    public function getGroupId()
    {
        return $this->groupId;
    }

    /**
     * Set status
     *
     * @param boolean $activeDirectory
     * @return AccountRequests
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return boolean 
     */
    public function getStatus()
    {
        return $this->status;
    }
}
