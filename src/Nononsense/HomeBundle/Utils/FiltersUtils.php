<?php
declare(strict_types=1);

namespace Nononsense\HomeBundle\Utils;

use Symfony\Component\HttpFoundation\Request;

class FiltersUtils
{

    public static function paginationFilters(array &$filters, int $page, int $limitMany = 20)
    {
        $filters["limit_from"] = ($page === 0)
                                    ? 0
                                    : $page - 1
        ;
        $filters["limit_many"]=$limitMany;

    }

    public static function requestToFilters(Request $request, array &$filters, array $fields)
    {
        forEach($fields as $field) {
            if ($request->get($field)) {
                $filters[$field] = $request->get($field);
            }
        }
    }
}