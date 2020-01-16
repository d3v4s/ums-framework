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
class SiteMapGeneratorController extends SettingsBaseController {
    public function __construct(PDO $conn, array $appConfig, string $layout=UMS_LAYOUT) {
        parent::__construct($conn, $appConfig, $layout);
    }

    /* ##################################### */
    /* PUBLIC FUNCTIONS */
    /* ##################################### */

    /* ########## SHOW FUNCTIONS ########## */

    /* function to view a site map update page */
    public function showSiteMapUpdate() {
        /* redirects */
        $this->redirectIfCanNotGenerateSiteMap();
        $this->redirectIfSiteMapNotExists();

        /* add javascript sources */
        array_push($this->jsSrcs,
            [SOURCE => '/js/utils/ums/adm-site-map.js'],
            [SOURCE => '/js/utils/ums/adm-site-map-updt.js']
        );

        /* get data from data factory instance */
        $data = SiteMapDataFactory::getInstance($this->appConfig)->getDataBySitemap();
        /* if not data, show error message and return */
        if (!$data) {
            $this->content = $this->showMessage('ERROR TO LOAD SITEMAP');
            return;
        }

        $this->content = view('ums/site-map-update', $data);
    }

    /* function to view site map generator page */
    public function showSiteMapGenerator() {
        /* redirect */
        $this->redirectIfCanNotGenerateSiteMap();

        /* add javascript sources */
        array_push($this->jsSrcs,
            [SOURCE => '/js/utils/ums/adm-site-map.js'],
            [SOURCE => '/js/utils/ums/adm-site-map-gen.js']
        );

        /* get data form data factory */
        $data = SiteMapDataFactory::getInstance($this->appConfig)->getDataByRoutes();

        $this->content = view('ums/site-map-generator', $data);
    }

    /* ########## ACTION FUNCTIONS ########## */

    /* fucntion to genrate a site map */
    public function generateSiteMap() {
        /* redirect */
        $this->redirectIfCanNotGenerateSiteMap();

        /* get tokens */
        $tokens = $this->getPostSessionTokens(CSRF_GEN_SITEMAP);
        /* get url server and unset it from post */
        $urlServer = $_POST[URL_SERVER];
        unset($_POST[URL_SERVER]);
        /* get post data */
        $data = $_POST;

        /* get verifier instance, and check site map generation request */
        $verifier = SiteMapVerifier::getInstance($this->appConfig);
        $resSiteMapGen = $verifier->verifyGenerateSiteMap($urlServer, $data, $tokens);
        if ($resSiteMapGen[SUCCESS]) {
            /* if succcess save site map and save new result */
            $resSiteMapGen[SUCCESS] = $this->saveSiteMap($resSiteMapGen[ROUTES]);
            $resSiteMapGen[MESSAGE] = $resSiteMapGen[SUCCESS] ? 'Site map successfully generate' : 'Generation site map failed';
        }

        /* result data */
        $dataOut = [
            SUCCESS => $resSiteMapGen[SUCCESS],
            MESSAGE => $resSiteMapGen[MESSAGE] ?? NULL,
            ERROR=> $resSiteMapGen[ERROR] ?? NULL
        ];

        /* function to deffault response */
        $funcDefault = function($data) {
            if (isset($data[MESSAGE])) {
                $_SESSION[MESSAGE] = $data[MESSAGE];
                $_SESSION[SUCCESS] = $data[SUCCESS];
            }
            $data[SUCCESS] ? redirect('/'.SITE_MAP_UPDATE_ROUTE) : redirect('/'.SITE_MAP_GENERATOR_ROUTE);
        };

        $this->switchResponse($dataOut, (!$resSiteMapGen[SUCCESS] && $resSiteMapGen[GENERATE_TOKEN]), $funcDefault, CSRF_GEN_SITEMAP);
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
        $urlset = $domTree->createElement('urlset');
        $urlset->setAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');
        $domTree->appendChild($urlset);

        /* append routes on urlset element */
        foreach ($routes as $route) {
            $url = $domTree->createElement('url');
            $loc = $domTree->createElement('loc', $route[ROUTE]);
            $url->appendChild($loc);
            if (isset($route[LASTMOD])) {
                $lastmod = $domTree->createElement('lastmod', $route[LASTMOD]);
                $url->appendChild($lastmod);
            }
            if (isset($route[CHANGEFREQ])) {
                $changefreq = $domTree->createElement('changefreq', $route[CHANGEFREQ]);
                $url->appendChild($changefreq);
            }
            if (isset($route[PRIORITY])) {
                $priority = $domTree->createElement('priority', $route[PRIORITY]);
                $url->appendChild($priority);
            }
            $urlset->appendChild($url);
        }

        /* save xml and return id success or not */
        $xml = $domTree->saveXML();
        return safeFileRewrite(getPath(getcwd(), 'public', 'sitemap.xml'), $xml);
    }

    /* function to redirect if sitemap not exists */
    private function redirectIfSiteMapNotExists() {
        if (!siteMapExists()) redirect('/'.SITE_MAP_GENERATOR_ROUTE);
    }

    /* function to redirect if user can not generate site map */
    private function redirectIfCanNotGenerateSiteMap() {
        if (!$this->userRole[CAN_GENERATE_SITEMAP]) $this->switchFailResponse();
    }
}

