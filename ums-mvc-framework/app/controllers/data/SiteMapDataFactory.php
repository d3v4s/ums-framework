<?php
namespace app\controllers\data;

/**
 * Class data factory,
 * used for generate and manage the response data of site map generator
 * @author Andrea Serra (DevAS) https://devas.info
 */
class SiteMapDataFactory extends DataFactory {
    protected $changefreqList = [];

    protected function __construct(array $appConfig) {
        parent::__construct($appConfig);
        $this->changefreqList = getList('changefreq');
    }

    /* ##################################### */
    /* PUBLIC FUNCTIONS */
    /* ##################################### */

    /* function to set the list of change frequency */
    public function setChangefreqList(array $changefreqList) {
        $this->changefreqList = $changefreqList;
    }

    /* function to get data by sitemap */
    public function getDataBySitemap() {
        /* load sitemap */
        if (!($routesXML = simplexml_load_file(getcwd().'/public/sitemap.xml') or FALSE)) return FALSE;

        /* create data routes */
        $routes = [];
        $nroutes = $routesXML->count();
        for ($i = 0; $i < $nroutes; $i++) {
            $routes[$i]['loc'] = $routesXML->url[$i]->loc;
            $routes[$i]['lastmod'] = isset($routesXML->url[$i]->lastmod) ? $routesXML->url[$i]->lastmod->__toString() : '';
            $routes[$i]['changefreq'] = isset($routesXML->url[$i]->changefreq) ? $routesXML->url[$i]->changefreq->__toString() : '';
            $routes[$i]['priority'] = isset($routesXML->url[$i]->priority) ? $routesXML->url[$i]->priority->__toString() : '';
        }

        /* remove domain from routes and return the results */
        $domain = $this->removeDomainInRoutes($routes);
        return [
            'urlServer' => $domain,
            'routes' => $routes,
            'token' => generateToken('csrfSitemap'),
            'changefreqList' => $this->changefreqList
        ];
    }

    /* function get data by routes */
    public function getDataByRoutes(): array {
        return [
            'routes' => array_keys(getRoutes()['GET']),
            'token' => generateToken('csrfSitemap'),
            'urlServer' => getServerUrl(),
            'siteMapExists' => siteMapExists(),
            'changefreqList' => $this->changefreqList
        ];
    }

    /* ##################################### */
    /* PRIVATE FUNCTIONS */
    /* ##################################### */

    /* function to remove the domain from routes url */
    private function removeDomainInRoutes(array &$routes): string {
        $domain = getDomain($routes[0]['loc']);
        foreach ($routes as $key => $route) {
            $routes[$key]['loc'] = str_replace($domain, '', $route['loc']);
        }
        return $domain;
    }
}