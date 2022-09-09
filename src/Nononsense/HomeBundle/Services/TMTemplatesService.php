<?php
declare(strict_types=1);

namespace Nononsense\HomeBundle\Services;

use Nononsense\HomeBundle\Entity\RetentionCategories;
use Nononsense\HomeBundle\Entity\TMTemplates;
use Nononsense\UtilsBundle\Classes\Utils;

class TMTemplatesService
{

    public static function getTheMostRestrictiveCategoryByTemplateId(TMTemplates $template)
    {
        $mostRestrictiveCategory = null;
        $retentions = $template->getRetentions();

        if(!is_null($retentions) && count($retentions) > 0) {
            $mostRestrictiveCategory = $retentions[0];
            $maxRetentionDays = $mostRestrictiveCategory->getRetentionDays();
            if(count($retentions) > 2) {
                /** @var RetentionCategories $retention */
                foreach($retentions as $retention) {
                    if ($retention->getRetentionDays() > $maxRetentionDays) {
                        $maxRetentionDays = $retention->getRetentionDays();
                        $mostRestrictiveCategory = $retention;

                    }
                }
            }
        }

        return $mostRestrictiveCategory;
    }
}