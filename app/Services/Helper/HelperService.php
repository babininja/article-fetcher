<?php

namespace App\Services\Helper;

class HelperService
{
    /**
     * @param $url
     * @return mixed|null
     */
    public function getPageNumber($url): mixed
    {
        if ($url) {
            parse_str(parse_url($url, PHP_URL_QUERY), $op);

            return array_key_exists('page', $op) ? $op['page'] : null;
        }

        return null;
    }
}
