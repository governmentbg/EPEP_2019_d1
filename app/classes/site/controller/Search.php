<?php
namespace site\controller;

use vakata\http\Uri as Url;
use vakata\http\Request;
use vakata\http\Response;
use League\Plates\Engine as Views;
use site\data\SearchService;
use site\Page as PageI;
use site\data\BannersService;

class Search extends Page
{
    protected $search;

    public function __construct(Views $views, BannersService $banners, SearchService $search)
    {
        parent::__construct($views, $banners);
        $this->search = $search;
    }
    public function __invoke(PageI $page, Url $url, Request $req, Response $res): Response
    {
        $q = $req->getQuery('q');
        $p = (int)max(1, $req->getQuery('p', 1, 'int'));
        $l = isset($page->content['perpage']) && (int)$page->content['perpage'] ? (int)$page->content['perpage'] : 10;
        $c = $req->getQuery('category', '');
        if (strlen($q) > 3) {
            $r = $this->search->search($q, $c, $p, $l);
        } else {
            $r = ['items' => [], 'count' => 0];
        }
        return $res->setBody(
            $this->views->render(
                'search',
                array_merge(
                    $this->viewParams($page),
                    $r,
                    [
                    'q' => $q,
                    'perpage' => $l,
                    'currentPage' => $p,
                    'category'  => $c,
                    'categories'  => $this->search->getCategories()
                    ]
                )
            )
        );
    }
}
