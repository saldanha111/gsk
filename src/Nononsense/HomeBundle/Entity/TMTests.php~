<?php
namespace Nononsense\HomeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity
 * @ORM\Table(name="tm_tests")
 * @ORM\Entity(repositoryClass="Nononsense\HomeBundle\Entity\TMTestsRepository")
 * @ORM\HasLifecycleCallbacks
 */
class TMTests
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="\Nononsense\HomeBundle\Entity\TMSignatures", inversedBy="tmTests")
     * @ORM\JoinColumn(name="signature_id", referencedColumnName="id", nullable=true)
     */
    protected $signature;

    /**
     * @ORM\ManyToOne(targetEntity="\Nononsense\HomeBundle\Entity\TMTestResults", inversedBy="tmTests")
     * @ORM\JoinColumn(name="reult_id", referencedColumnName="id", nullable=true)
     */
    protected $result;

    /**
     * @ORM\OneToMany(targetEntity="\Nononsense\HomeBundle\Entity\TMSignatures", mappedBy="tmTest")
     */
    protected $tmSignatures;

    /**
     * @var string
     *
     * @ORM\Column(name="test", type="text")
     */
    protected $test;

    /**
     * @var string
     *
     * @ORM\Column(name="token", type="string")
     */
    protected $token;

     /**
     * @var integer
     *
     * @ORM\Column(name="test_id", type="integer", nullable=true)
     */
    protected $test_id;

    /**
     * @ORM\ManyToOne(targetEntity="\Nononsense\UserBundle\Entity\Users", inversedBy="tmTests")
     * @ORM\JoinColumn(name="userid", referencedColumnName="id")
     */
    protected $userEntiy;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $created;

    
    public function __construct()
    {

    }

    /**
     * @ORM\PrePersist
     */
    public function setCreatedValue()
    {
        if (!$this->created) {
            $this->created = new \DateTime();
        }
        $this->modified = $this->created;
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
     * Set created
     *
     * @param \DateTime $created
     * @return TMSignatures
     */
    public function setCreated($created)
    {
        $this->created = $created;

        return $this;
    }

    /**
     * Get created
     *
     * @return \DateTime 
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Set test
     *
     * @param string $test
     * @return TMTests
     */
    public function setTest($test)
    {
        $this->test = $test;

        return $this;
    }

    /**
     * Get test
     *
     * @return string 
     */
    public function getTest()
    {
        return $this->test;
    }

    /**
     * Set token
     *
     * @param string $token
     * @return TMTests
     */
    public function setToken($token)
    {
        $this->token = $token;

        return $this;
    }

    /**
     * Get token
     *
     * @return string 
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * Set signature
     *
     * @param \Nononsense\HomeBundle\Entity\TMSignatures $signature
     * @return TMTests
     */
    public function setSignature(\Nononsense\HomeBundle\Entity\TMSignatures $signature = null)
    {
        $this->signature = $signature;

        return $this;
    }

    /**
     * Get signature
     *
     * @return \Nononsense\HomeBundle\Entity\TMSignatures 
     */
    public function getSignature()
    {
        return $this->signature;
    }

    /**
     * Set result
     *
     * @param \Nononsense\HomeBundle\Entity\TMTestResults $result
     * @return TMTests
     */
    public function setResult(\Nononsense\HomeBundle\Entity\TMTestResults $result = null)
    {
        $this->result = $result;

        return $this;
    }

    /**
     * Get result
     *
     * @return \Nononsense\HomeBundle\Entity\TMTestResults 
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * Set userEntiy
     *
     * @param \Nononsense\UserBundle\Entity\Users $userEntiy
     * @return TMTests
     */
    public function setUserEntiy(\Nononsense\UserBundle\Entity\Users $userEntiy = null)
    {
        $this->userEntiy = $userEntiy;

        return $this;
    }

    /**
     * Get userEntiy
     *
     * @return \Nononsense\UserBundle\Entity\Users 
     */
    public function getUserEntiy()
    {
        return $this->userEntiy;
    }

    /**
     * Set test_id
     *
     * @param integer $testId
     * @return TMTests
     */
    public function setTestId($testId)
    {
        $this->test_id = $testId;

        return $this;
    }

    /**
     * Get test_id
     *
     * @return integer 
     */
    public function getTestId()
    {
        return $this->test_id;
    }

    /**
     * Add tmSignatures
     *
     * @param \Nononsense\HomeBundle\Entity\TMSignatures $tmSignatures
     * @return TMTests
     */
    public function addTmSignature(\Nononsense\HomeBundle\Entity\TMSignatures $tmSignatures)
    {
        $this->tmSignatures[] = $tmSignatures;

        return $this;
    }

    /**
     * Remove tmSignatures
     *
     * @param \Nononsense\HomeBundle\Entity\TMSignatures $tmSignatures
     */
    public function removeTmSignature(\Nononsense\HomeBundle\Entity\TMSignatures $tmSignatures)
    {
        $this->tmSignatures->removeElement($tmSignatures);
    }

    /**
     * Get tmSignatures
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getTmSignatures()
    {
        return $this->tmSignatures;
    }
}
