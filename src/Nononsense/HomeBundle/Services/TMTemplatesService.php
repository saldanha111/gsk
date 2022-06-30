<?php
declare(strict_types=1);

namespace Nononsense\HomeBundle\Services;

use Nononsense\HomeBundle\Entity\RetentionCategories;
use Nononsense\HomeBundle\Entity\TMTemplates;

class TMTemplatesService
{

    public static function getTheMostRestrictiveCategoryByTemplateId(TMTemplates $template): RetentionCategories
    {
        $retentions = $template->getRetentions();
        $maxRetentionDays = $retentions[0]->getRetentionDays();
        $mostRestrictiveCategory = $retentions[0];
        /** @var RetentionCategories $retention */
        foreach($retentions as $retention) {
            if ($retention->getRetentionDays() > $maxRetentionDays) {
                $maxRetentionDays = $retention->getRetentionDays();
                $mostRestrictiveCategory = $retention;
            }
        }
        return $mostRestrictiveCategory;
    }
}