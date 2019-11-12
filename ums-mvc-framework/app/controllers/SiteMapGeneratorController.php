<?php
namespace app\controllers;

use \PDO;
use \DOMDocument;
use app\controllers\verifiers\SiteMapVerifier;
use app\controllers\data\SiteMapDataFactory;

class SiteMapGeneratorController extends Controller {
    public function __construct(PDO $conn, array $appConfig, string $layout = 'ums') {
        parent::__construct($conn, $appConfig, $layout);
    }

    public function showSiteMapUpdate() {
        $this->redirectIfCanNotGenerateSiteMap();
        $this->redirectIfSiteMapNotExists();

        array_push($this->jsSrcs,
            ['src' => '/js/utils/ums/adm-site-map.js'],
            ['src' => '/js/utils/ums/adm-site-map-updt.js']
        );

        $data = SiteMapDataFactory::getInstance($this->appConfig)->getDataBySitemap();
        if (!$data) {
            $this->content = $this->showMessage('ERROR TO LOAD SITEMAP');
            return;
        }
        
        $this->content = view('ums/admin-site-map-update', $data);
    }

    public function showSiteMapGenerator() {
        $this->redirectIfCanNotGenerateSiteMap();

        array_push($this->jsSrcs,
            ['src' => '/js/utils/ums/adm-site-map.js'],
            ['src' => '/js/utils/ums/adm-site-map-gen.js']
        );

        $data = SiteMapDataFactory::getInstance($this->appConfig)->getDataByRoutes();
        $this->content = view('ums/admin-site-map-generator', $data);
    }

    public function siteMapGenerate() {
        $this->redirectIfCanNotGenerateSiteMap();

        $tokens = $this->getPostSessionTokens('_xf', 'csrfSitemap');
        $urlServer = $_POST['url-server'];
        unset($_POST['url-server']);
        $data = $_POST;

        $verifier = SiteMapVerifier::getInstance($this->appConfig);
        $resSiteMapGen = $verifier->verifySiteMapGenerate($urlServer, $data, $tokens);
        if ($resSiteMapGen['success']) {
            $resSiteMapGen['success'] = $this->saveSiteMap($resSiteMapGen['routesList']);
            $resSiteMapGen['message'] = $resSiteMapGen['success'] ? 'Site map successfully generate' : 'Generation site map failed';
        }

        $header = strtoupper($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '');
        switch ($header) {
            case 'XMLHTTPREQUEST':
                $resJSON = [
                    'success' => $resSiteMapGen['success'],
                    'message' => $resSiteMapGen['message'] ?? NULL,
                    'error'=> $resSiteMapGen['error'] ?? NULL
                ];
                if (!$resSiteMapGen['success']) $resJSON['ntk'] = generateToken('csrfSitemap');
                echo json_encode($resJSON);
                exit;
            default:
                if (isset($resSiteMapGen['message'])) {
                    $_SESSION['message'] = $resSiteMapGen['message'];  
                    $_SESSION['success'] = $resSiteMapGen['success'];
                }
                $resSiteMapGen['success'] ? redirect('/ums/generator/site/map/update') : redirect('/ums/generator/site/map');
                return;
        }
    }

    private function saveSiteMap(array $routes): bool {
        $domTree = new DOMDocument('1.0', 'UTF-8');
        $domTree->formatOutput = TRUE;

        $urlSet = $domTree->createElement('urlset');
        $urlSet->setAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');
        $domTree->appendChild($urlSet);

        foreach ($routes as $route) {
            $url = $domTree->createElement('url');
            $loc = $domTree->createElement('loc', $route['route']);
            $url->appendChild($loc);
            if (isset($route['lastmod'])) {
                $lastmod = $domTree->createElement('lastmod', $route['lastmod']);
                $url->appendChild($lastmod);
            }
            if (isset($route['changefreq'])) {
                $changefreq = $domTree->createElement('changefreq', $route['changefreq']);
                $url->appendChild($changefreq);
            }
            if (isset($route['priority'])) {
                $priority = $domTree->createElement('priority', $route['priority']);
                $url->appendChild($priority);
            }
            $urlSet->appendChild($url);
        }

        $xml = $domTree->saveXML();
        return safeFileRewrite(getcwd().'/public/sitemap.xml', $xml);
    }

    private function redirectIfSiteMapNotExists() {
        if (!siteMapExists()) redirect('/ums/generator/site/map');
    }

    private function redirectIfCanNotGenerateSiteMap() {
        if (!userCanGenerateSiteMap()) redirect();
    }
}

