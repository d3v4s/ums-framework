<?php
namespace app\controllers\verifiers;

class SiteMapVerifier extends Verifier {
    protected $changefreqList =  [];

    protected function __construct(array $appConfig) {
        parent::__construct($appConfig);
        $this->changefreqList = getList('changefreq');
    }

    public function setChangefreqList(array $changefreqList) {
        $this->changefreqList = $changefreqList;
    }

    public function verifySiteMapGenerate(string $urlServer, array $data, array $tokens): array {
        $result = [
            'message' => 'Site map creation failed',
            'success' => FALSE
        ];

        if (!$this->verifyTokens($tokens)) {
//             $result['message'] = 'FUCK';
            return $result;
        }

        if (!$this->isValidDomain($urlServer)) {
            $result['message'] = 'Invalid url server';
            $result['error'] = 'url-server';
            return $result;
        }

        $routesList = $this->getRoutesList($data);
        $keys = array_keys($routesList);
        foreach ($keys as $key) {
            if (!empty($data['lastmod-' . $key])) {
                $lastmod = $data['lastmod-' . $key];
                if (!$this->isValidDate($lastmod)) {
                    $result['message'] = 'Last modification date invalid on route ' . $key;
                    $result['error'] = 'lastmod-' . $key;
                    return $result;
                }
                $this->escapeEntity($lastmod);
                $routesList[$key]['lastmod'] = $lastmod;
            }
            if (!empty($data['priority-' . $key])) {
                $priority = $data['priority-' . $key];
                if (!$this->isValidNumber($priority, 0, 1)) {
                    $result['message'] = 'Invalid priority on route ' . $key;
                    $result['error'] = 'priority-' . $key;
                    return $result;
                }
                $this->escapeEntity($priority);
                $routesList[$key]['priority'] = $priority;
            }
            if (!empty($data['changefreq-' . $key])) {
                $changefreq = $data['changefreq-' . $key];
                if (!in_array($changefreq, $this->changefreqList)) {
                    $result['message'] = 'Change frequency invalid on route ' . $key;
                    $result['error'] = 'changefreq-' . $key;
                    return $result;
                }
                $this->escapeEntity($changefreq);
                $routesList[$key]['changefreq'] = $changefreq;
            }
            $routesList[$key]['route'] = $urlServer . $routesList[$key]['route'];
            $this->escapeEntity($routesList[$key]['route']);
        }

        unset($result['message']);
        $result['routesList'] = $routesList;
        $result['success'] = TRUE;
        return $result;
    }

    private function getRoutesList(array $data): array {
        $routeList = [];
        foreach ($data as $key => $val) if (substr($key, 0, 6) === 'route-') $routeList[substr($key, 6)] = ['route' => $val];

        return $routeList;
    }

    private function isValidDate(string $date): bool {
        if (!preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/', $date)) return FALSE;
        $splitDate = explode('-', $date);
        return checkdate($splitDate[1], $splitDate[2], $splitDate[0]);
    }

    private function escapeEntity(string &$string) {
        $string = str_replace('&', '&amp;', $string);
        $string = str_replace("'", '&apos;', $string);
        $string = str_replace('"', '&quot;', $string);
        $string = str_replace('>', '&gt;', $string);
        $string = str_replace('<', '&lt;', $string);
    }
}

