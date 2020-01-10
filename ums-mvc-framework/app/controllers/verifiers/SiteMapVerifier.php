<?php
namespace app\controllers\verifiers;

/**
 * Class verifier, to validate a generate sitemap request
 * @author Andrea Serra (DevAS) https://devas.info
 */
class SiteMapVerifier extends Verifier {

    protected function __construct() {
        parent::__construct();
    }

    /* ##################################### */
    /* PUBLIC FUNCTIONS */
    /* ##################################### */

    /* function to verfify generate sitemap request */
    public function verifyGenerateSiteMap(string $urlServer, array $data, array $tokens): array {
        /* set fail result */
        $result = [
            MESSAGE => 'Site map creation failed',
            SUCCESS => FALSE,
            GENERATE_TOKEN => FALSE
        ];

        /* validate tokens */
        if (!$this->verifyTokens($tokens)) return $result;
        $result[GENERATE_TOKEN] = TRUE;

        /* validate domain */
        if (!$this->isValidDomain($urlServer)) {
            $result[MESSAGE] = 'Invalid url server';
            $result[ERROR] = URL_SERVER;
            return $result;
        }

        /* get routes from data */
        $routesList = $this->getRoutesList($data);

        /* get keys of list and iterate it */
        $keys = array_keys($routesList);
        foreach ($keys as $key) {
            /* if not empty set last modify */
            if (!empty($data[SITEMAP_LASTMOD.$key])) {
                /* get last modify date */
                $lastmod = $data[SITEMAP_LASTMOD.$key];
                /* validate last modify date */
                if (!$this->isValidDate($lastmod)) {
                    $result[MESSAGE] = 'Last modification date invalid on route '.$key;
                    $result[ERROR] = SITEMAP_LASTMOD.$key;
                    return $result;
                }

                /* escape malicious chars and set last modify date */
                $this->escapeEntity($lastmod);
                $routesList[$key][LASTMOD] = $lastmod;
            }

            /* if not empty set priority */
            if (!empty($data[SITEMAP_PRIORITY.$key])) {
                /* get priority number */
                $priority = $data[SITEMAP_PRIORITY.$key];
                /* validate priority number */
                if (!$this->isValidNumber($priority, 0, 1)) {
                    $result[MESSAGE] = 'Invalid priority on route '.$key;
                    $result[ERROR] = SITEMAP_PRIORITY.$key;
                    return $result;
                }

                /* escape malicious chars and set priority */
                $this->escapeEntity($priority);
                $routesList[$key][PRIORITY] = $priority;
            }

            /* if not empty set change frequency */
            if (!empty($data[SITEMAP_CHANGEFREQ.$key])) {
                /* get change frequency */
                $changefreq = $data[SITEMAP_CHANGEFREQ.$key];
                /* validate change frequency */
                if (!in_array($changefreq, CHANGE_FREQ_LIST)) {
                    $result[MESSAGE] = 'Change frequency invalid on route '.$key;
                    $result[ERROR] = SITEMAP_CHANGEFREQ.$key;
                    return $result;
                }

                /* escape malicious chars and set change frequency */
                $this->escapeEntity($changefreq);
                $routesList[$key][CHANGEFREQ] = $changefreq;
            }

            /* escape malicious chars and set priority */
            $routesList[$key][ROUTE] = $urlServer . $routesList[$key][ROUTE];
            $this->escapeEntity($routesList[$key][ROUTE]);
        }

        /* unset error message and set results */
        unset($result[MESSAGE]);
        $result[ROUTES] = $routesList;
        $result[SUCCESS] = TRUE;

        /* return results */
        return $result;
    }

    /* ##################################### */
    /* PRIVATE FUNCTIONS */
    /* ##################################### */

    /* function to get routes list */
    private function getRoutesList(array $data): array {
        $routeList = [];
        foreach ($data as $key => $val) if (substr($key, 0, 6) === SITEMAP_ROUTE) $routeList[substr($key, 6)] = [ROUTE => $val];

        return $routeList;
    }

    /* function to validate a date */
    private function isValidDate(string $date): bool {
        if (!preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/', $date)) return FALSE;
        $splitDate = explode('-', $date);
        return checkdate($splitDate[1], $splitDate[2], $splitDate[0]);
    }

    /* function to escape sitemap special chars */
    private function escapeEntity(string &$string) {
        $string = str_replace('&', '&amp;', $string);
        $string = str_replace("'", '&apos;', $string);
        $string = str_replace('"', '&quot;', $string);
        $string = str_replace('>', '&gt;', $string);
        $string = str_replace('<', '&lt;', $string);
    }
}

