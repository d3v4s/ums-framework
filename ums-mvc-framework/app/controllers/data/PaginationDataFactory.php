<?php

namespace app\controllers\data;

use \PDO;

/**
 * Class data factory, used for generate
 * and manage the data of response of user
 * management system
 * @author Andrea Serra (DevAS) https://devas.info
 */
class PaginationDataFactory extends DataFactory {

    protected function __construct(array $langData, PDO $conn=NULL) {
        parent::__construct($langData, $conn);
    }

    /* ##################################### */
    /* PROTECTED FUNCTIONS */
    /* ##################################### */

    /* function to get the order direction */
    protected function getOrderDirection(string $orderDir): string {
        return in_array($orderDir, ORDER_DIR_LIST) ? $orderDir : DESC;
    }

    /* function to get the order by */
    protected function getOrderBy(string $orderBy=NULL, array $orderByList, string $orderByDefault): string {
        $orderBy = $orderBy ?? $orderByDefault;
        return in_array($orderBy, $orderByList) ? $orderBy : $orderByDefault;
    }

    protected function getSearchQuery(string $search):  string {
        return empty($search) ? '' : '?'.SEARCH."=$search";
    }
    /* function to get search data */
    protected function getSearchData(string $search, string $baseLinkPagin, string $closeUrl): array {
        return [
            SEARCH => $search,
            SEARCH_QUERY => $this->getSearchQuery($search),
            SEARCH_ACTION => "{$baseLinkPagin}1$closeUrl"
        ];
    }

    /* function to get pagination data */
    protected function getPaginationData(string $orderBy, string $orderDir, int $page, int $rowsForPage, int $totRows, string $baseUrl, string $searchQuery=''): array {
        /* calc users for page and n. pages */
        $rowsForPage = in_array($rowsForPage, ROWS_FOR_PAGE_LIST) ? $rowsForPage : DEFAULT_ROWS_FOR_PAGE;
        $maxPages = (int) ceil($totRows/$rowsForPage);
        $maxPages = $maxPages < 1 ? 1 : $maxPages;
        $page = $page > $maxPages ? $maxPages : $page;
        $page = $page <= 0 ? 1 : $page;
        
        /* calc start and stop page of pagination */
        $startPage = $page - intdiv(LINK_PAGINATION, 2);
        $startPage = (int) $startPage > ($maxPages - LINK_PAGINATION) ? $maxPages - LINK_PAGINATION : $startPage;
        $startPage = $startPage <= 0 ? 1 : $startPage;
        $stopPage = $startPage + LINK_PAGINATION;
        $stopPage = $stopPage >= $maxPages ? $maxPages : $stopPage;

        /* set url closer and the base of pagination link */
        $closeUrl = '/' . $rowsForPage . $searchQuery;
        $baseLinkPagination = $baseUrl."/$orderBy/$orderDir/";
        
        /* set link and class of pagination arrow left */
        $linkPaginationArrowLeft = $baseLinkPagination . ($page-1) . $closeUrl;
        $classPaginationArrowLeft = $page === 1 ? DISABLED : '';
        
        /* set link and class of pagination arrow right */
        $linkPaginationArrowRight = $baseLinkPagination . ($page+1) . $closeUrl;
        $classPaginationArrowRight = $page === $maxPages ? DISABLED : '';

        /* return data */
        return [
            ROWS_FOR_PAGE => $rowsForPage,
            PAGE => $page,
            MAX_PAGES => $maxPages,
            START_PAGE => $startPage,
            STOP_PAGE => $stopPage,
            CLOSE_LINK_PAGIN => $closeUrl,
            BASE_LINK_PAGIN => $baseLinkPagination,
            LINK_PAGIN_ARROW_LEFT => $linkPaginationArrowLeft,
            CLASS_PAGIN_ARROW_LEFT => $classPaginationArrowLeft,
            LINK_PAGIN_ARROW_RIGHT => $linkPaginationArrowRight,
            CLASS_PAGIN_ARROW_RIGHT => $classPaginationArrowRight,
            BASE_LINK_ROWS_FOR_PAGE => "$baseLinkPagination$page/"
        ];
    }

    public function getPaginationDefaultData(): array {
        return [
            SEARCH => '',
            SEARCH_QUERY => '',
            ROWS_FOR_PAGE => 10,
            PAGE => 1,
            MAX_PAGES => 1,
            START_PAGE => 1,
            STOP_PAGE => 1,
            CLOSE_LINK_PAGIN => '',
            BASE_LINK_PAGIN => '',
            LINK_PAGIN_ARROW_LEFT => '',
            CLASS_PAGIN_ARROW_LEFT => DISABLED,
            LINK_PAGIN_ARROW_RIGHT => '',
            CLASS_PAGIN_ARROW_RIGHT => DISABLED,
            BASE_LINK_ROWS_FOR_PAGE => ''
        ];
    }
}
