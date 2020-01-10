<?php
namespace app\controllers\data;

/**
 * Class data factory,
 * used for generate and manage the response data of site map generator
 * @author Andrea Serra (DevAS) https://devas.info
 */
class SiteMapDataFactory extends DataFactory {
    protected $changefreqList = [];

    protected function __construct() {
        parent::__construct();
        $this->changefreqList = getList(CHANGE_FREQ_LIST);
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
        if (!($routesXML = simplexml_load_file(getPath(getcwd(), 'public', 'sitemap.xml')) or FALSE)) return FALSE;

        /* create data routes */
        $routes = [];
        $nroutes = $routesXML->count();
        for ($i = 0; $i < $nroutes; $i++) {
            $routes[$i][LOCATION] = $routesXML->url[$i]->loc;
            $routes[$i][LASTMOD] = isset($routesXML->url[$i]->lastmod) ? $routesXML->url[$i]->lastmod->__toString() : '';
            $routes[$i][CHANGEFREQ] = isset($routesXML->url[$i]->changefreq) ? $routesXML->url[$i]->changefreq->__toString() : '';
            $routes[$i][PRIORITY] = isset($routesXML->url[$i]->priority) ? $routesXML->url[$i]->priority->__toString() : '';
        }

        /* remove domain from routes and return the results */
        $domain = $this->removeDomainInRoutes($routes);
        return [
            URL_SERVER => $domain,
            ROUTES => $routes,
            TOKEN => generateToken(CSRF_GEN_SITEMAP),
            CHANGE_FREQ_LIST => $this->changefreqList
        ];
    }

    /* function get data by routes */
    public function getDataByRoutes(): array {
        return [
            ROUTES => array_keys(getRoutes()['GET']),
            TOKEN => generateToken(CSRF_GEN_SITEMAP),
            URL_SERVER => getServerUrl(),
            SITE_MAP_EXISTS => siteMapExists(),
            CHANGE_FREQ_LIST => $this->changefreqList
        ];
    }

    /* ##################################### */
    /* PRIVATE FUNCTIONS */
    /* ##################################### */

    /* function to remove the domain from routes url */
    private function removeDomainInRoutes(array &$routes): string {
        $domain = getDomain($routes[0][LOCATION]);
        foreach ($routes as $key => $route) {
            $routes[$key][LOCATION] = str_replace($domain, '', $route[LOCATION]);
        }
        return $domain;
    }
}