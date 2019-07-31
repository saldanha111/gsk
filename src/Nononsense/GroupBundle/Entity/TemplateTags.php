<?php

namespace Nononsense\GroupBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * TemplateTags
 */
class TemplateTags
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var \Nononsense\DocumentBundle\Entity\Templates
     */
    private $template;

    /**
     * @var \Nononsense\DocumentBundle\Entity\Tags
     */
    private $tag;


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
     * Set template
     *
     * @param \Nononsense\DocumentBundle\Entity\Templates $template
     * @return TemplateTags
     */
    public function setTemplate(\Nononsense\DocumentBundle\Entity\Templates $template = null)
    {
        $this->template = $template;

        return $this;
    }

    /**
     * Get template
     *
     * @return \Nononsense\DocumentBundle\Entity\Templates 
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * Set tag
     *
     * @param \Nononsense\DocumentBundle\Entity\Tags $tag
     * @return TemplateTags
     */
    public function setTag(\Nononsense\DocumentBundle\Entity\Tags $tag = null)
    {
        $this->tag = $tag;

        return $this;
    }

    /**
     * Get tag
     *
     * @return \Nononsense\DocumentBundle\Entity\Tags 
     */
    public function getTag()
    {
        return $this->tag;
    }
}
