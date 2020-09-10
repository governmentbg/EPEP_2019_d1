<?php

namespace site\controller;

use vakata\http\Uri as Url;
use vakata\http\Request;
use vakata\http\Response;
use League\Plates\Engine as Views;
use site\Page as PageI;
use site\PageNotFoundException;
use site\data\ZopService;
use site\data\BannersService;

class Zop extends Page
{
    protected $zop;

    public function __construct(Views $views, BannersService $banners, ZopService $zop)
    {
        parent::__construct($views, $banners);
        $this->zop = $zop;
    }
    public function __invoke(PageI $page, Url $url, Request $req, Response $res): Response
    {
        $categories = $this->zop->categories();
        $category = isset($page->content['category']) && isset($categories[$page->content['category']]) ?
            (int) $page->content['category'] :
            (int) key($categories);
        if ($url->getSegment(2) !== null) {
            if (!(int)$url->getSegment(2)) {
                throw new PageNotFoundException();
            }
            $zop = $this->zop->single((int)$url->getSegment(2), $category);
            if ($zop === null) {
                throw new PageNotFoundException();
            }
            return $res->setBody(
                $this->views->render(
                    'zop',
                    array_merge($this->viewParams($page), [ 'zop' => $zop, 'categories' => $categories ])
                )
            );
        }

        // render the listing
        $p = (int) max(1, $req->getQuery('p', 1, 'int'));
        $l = (int) $page->content['perpage'] ? (int) $page->content['perpage'] : 10;
        $zops = $this->zop->listing($category, $p, $l);

        return $res->setBody(
            $this->views->render(
                'zoplist',
                array_merge($this->viewParams($page), $zops, [ 'perpage' => $l, 'currentPage' => $p ])
            )
        );
    }
}
