<?php
namespace app\controllers;

use \PDO;
use \DOMDocument;
use app\controllers\verifiers\SiteMapVerifier;
use app\controllers\data\SiteMapDataFactory;

/**
 * Class controller to manage the site map generation and updates
 * @author Andrea Serra (DevAS) https://devas.info
 */
class SiteMapGeneratorController extends Controller {
    public function __construct(PDO $conn, array $appConfig, string $layout = 'ums') {
        parent::__construct($conn, $appConfig, $layout);
    }

    /* ##################################### */
    /* PUBLIC FUNCTIONS */
    /* ##################################### */

    /* function to view a site map update page */
    public function showSiteMapUpdate() {
        /* redirects */
        $this->redirectIfCanNotGenerateSiteMap();
        $this->redirectIfSiteMapNotExists();

        /* add javascript sources */
        array_push($this->jsSrcs,
            ['src' => '/js/utils/ums/adm-site-map.js'],
            ['src' => '/js/utils/ums/adm-site-map-updt.js']
        );

        /* get data from data factory instance */
        $data = SiteMapDataFactory::getInstance($this->appConfig)->getDataBySitemap();
        /* if not data, show error message and return */
        if (!$data) {
            $this->content = $this->showMessage('ERROR TO LOAD SITEMAP');
            return;
        }

        $this->content = view('ums/admin-site-map-update', $data);
    }

    /* function to view site map generator page */
    public function showSiteMapGenerator() {
        /* redirect */
        $this->redirectIfCanNotGenerateSiteMap();

        /* add javascript sources */
        array_push($this->jsSrcs,
            ['src' => '/js/utils/ums/adm-site-map.js'],
            ['src' => '/js/utils/ums/adm-site-map-gen.js']
        );

        /* get data form data factory */
        $data = SiteMapDataFactory::getInstance($this->appConfig)->getDataByRoutes();

        $this->content = view('ums/admin-site-map-generator', $data);
    }

    public function siteMapGenerate() {
        /* redirect */
        $this->redirectIfCanNotGenerateSiteMap();

        /* get tokens */
        $tokens = $this->getPostSessionTokens('XS_TKN', 'csrfSitemap');
        /* get url server and unset it from post */
        $urlServer = $_POST['url-server'];
        unset($_POST['url-server']);
        /* get post data */
        $data = $_POST;

        /* get verifier instance, and check site map generation request */
        $verifier = SiteMapVerifier::getInstance($this->appConfig);
        $resSiteMapGen = $verifier->verifySiteMapGenerate($urlServer, $data, $tokens);
        if ($resSiteMapGen['success']) {
            /* if succcess save site map and save new result */
            $resSiteMapGen['success'] = $this->saveSiteMap($resSiteMapGen['routesList']);
            $resSiteMapGen['message'] = $resSiteMapGen['success'] ? 'Site map successfully generate' : 'Generation site map failed';
        }

        /* result data */
        $dataOut = [
            'success' => $resSiteMapGen['success'],
            'message' => $resSiteMapGen['message'] ?? NULL,
            'error'=> $resSiteMapGen['error'] ?? NULL
        ];

        /* function to deffault response */
        $funcDefault = function($data) {
            if (isset($data['message'])) {
                $_SESSION['message'] = $data['message'];
                $_SESSION['success'] = $data['success'];
            }
            $data['success'] ? redirect('/ums/generator/site/map/update') : redirect('/ums/generator/site/map');
        };

        $this->switchResponse($dataOut, !$resSiteMapGen['success'], $funcDefault, 'csrfSitemap');
//         $header = strtoupper($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '');
//         switch ($header) {
//             case 'XMLHTTPREQUEST':
                
//                 if () $resJSON['ntk'] = generateToken('csrfSitemap');
//                 echo json_encode($resJSON);
//                 header("Content-Type: application/json");
//                 header("X-Content-Type-Options: nosniff");
//                 exit;
//             default:
                
//                 return;
//         }
    }

    /* ##################################### */
    /* PRIVATE FUNCTIONS */
    /* ##################################### */

    /* function to save a site map */
    private function saveSiteMap(array $routes): bool {
        /* init DOM document */
        $domTree = new DOMDocument('1.0', 'UTF-8');
        $domTree->formatOutput = TRUE;

        /* append urlset element */
        $urlSet = $domTree->createElement('urlset');
        $urlSet->setAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');
        $domTree->appendChild($urlSet);

        /* append routes on urlsety element */
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

        /* save xml and return id success or not */
        $xml = $domTree->saveXML();
        return safeFileRewrite(getcwd().'/public/sitemap.xml', $xml);
    }

    /* function to redirect if sitemap not exists */
    private function redirectIfSiteMapNotExists() {
        if (!siteMapExists()) redirect('/ums/generator/site/map');
    }

    /* function to redirect if user can not generate site map */
    private function redirectIfCanNotGenerateSiteMap() {
        if (!userCanGenerateSiteMap()) redirect();
    }
}

