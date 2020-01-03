<?php
namespace app\controllers\verifiers;

/**
 * Class verifier, to validate a generate sitemap request
 * @author Andrea Serra (DevAS) https://devas.info
 */
class SiteMapVerifier extends Verifier {
    protected $changefreqList =  [];

    protected function __construct(array $appConfig) {
        parent::__construct($appConfig);
        $this->changefreqList = getList('changefreq');
    }

    /* ##################################### */
    /* PUBLIC FUNCTIONS */
    /* ##################################### */

    /* set list of change frequencies */
    public function setChangefreqList(array $changefreqList) {
        $this->changefreqList = $changefreqList;
    }

    /* function to verfify generate sitemap request */
    public function verifySiteMapGenerate(string $urlServer, array $data, array $tokens): array {
        /* set fail result */
        $result = [
            'message' => 'Site map creation failed',
            'success' => FALSE
        ];

        /* validate tokens */
        if (!$this->verifyTokens($tokens)) return $result;

        /* validate domain */
        if (!$this->isValidDomain($urlServer)) {
            $result['message'] = 'Invalid url server';
            $result['error'] = 'url-server';
            return $result;
        }

        /* get routes from data */
        $routesList = $this->getRoutesList($data);

        /* get keys of list and iterate it */
        $keys = array_keys($routesList);
        foreach ($keys as $key) {
            /* if not empty set last modify */
            if (!empty($data['lastmod-' . $key])) {
                /* get last modify date */
                $lastmod = $data['lastmod-' . $key];
                /* validate last modify date */
                if (!$this->isValidDate($lastmod)) {
                    $result['message'] = 'Last modification date invalid on route ' . $key;
                    $result['error'] = 'lastmod-' . $key;
                    return $result;
                }

                /* escape malicious chars and set last modify date */
                $this->escapeEntity($lastmod);
                $routesList[$key]['lastmod'] = $lastmod;
            }

            /* if not empty set priority */
            if (!empty($data['priority-' . $key])) {
                /* get priority number */
                $priority = $data['priority-' . $key];
                /* validate priority number */
                if (!$this->isValidNumber($priority, 0, 1)) {
                    $result['message'] = 'Invalid priority on route ' . $key;
                    $result['error'] = 'priority-' . $key;
                    return $result;
                }

                /* escape malicious chars and set priority */
                $this->escapeEntity($priority);
                $routesList[$key]['priority'] = $priority;
            }

            /* if not empty set change frequency */
            if (!empty($data['changefreq-' . $key])) {
                /* get change frequency */
                $changefreq = $data['changefreq-' . $key];
                /* validate change frequency */
                if (!in_array($changefreq, $this->changefreqList)) {
                    $result['message'] = 'Change frequency invalid on route ' . $key;
                    $result['error'] = 'changefreq-' . $key;
                    return $result;
                }

                /* escape malicious chars and set change frequency */
                $this->escapeEntity($changefreq);
                $routesList[$key]['changefreq'] = $changefreq;
            }

            /* escape malicious chars and set priority */
            $routesList[$key]['route'] = $urlServer . $routesList[$key]['route'];
            $this->escapeEntity($routesList[$key]['route']);
        }

        /* unset error message and set results */
        unset($result['message']);
        $result['routesList'] = $routesList;
        $result['success'] = TRUE;

        /* return results */
        return $result;
    }

    /* ##################################### */
    /* PRIVATE FUNCTIONS */
    /* ##################################### */

    /* function to get routes list */
    private function getRoutesList(array $data): array {
        $routeList = [];
        foreach ($data as $key => $val) if (substr($key, 0, 6) === 'route-') $routeList[substr($key, 6)] = ['route' => $val];

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

