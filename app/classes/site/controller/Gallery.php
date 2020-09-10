<?php

namespace site\controller;

use vakata\http\Uri as Url;
use vakata\http\Request;
use vakata\http\Response;
use League\Plates\Engine as Views;
use site\Page as PageI;
use site\PageNotFoundException;
use site\data\GalleriesService;
use site\data\BannersService;

class Gallery extends Page
{
    protected $galleries;

    public function __construct(Views $views, BannersService $banners, GalleriesService $galleries)
    {
        parent::__construct($views, $banners);
        $this->galleries = $galleries;
    }
    public function __invoke(PageI $page, Url $url, Request $req, Response $res): Response
    {
        // render a single news item
        if ($url->getSegment(2) !== null) {
            if (!(int)$url->getSegment(2)) {
                throw new PageNotFoundException();
            }
            $gallery = $this->galleries->single($page->lang, (int)$url->getSegment(2));
            if ($gallery === null) {
                throw new PageNotFoundException();
            }
            return $res->setBody(
                $this->views->render(
                    'gallery',
                    array_merge($this->viewParams($page), [ 'gallery' => $gallery ])
                )
            );
        }

        // render the listing
        $p = (int)max(1, $req->getQuery('p', 1, 'int'));
        $l = (int)$page->content['perpage'] ? (int)$page->content['perpage'] : 10;
        $galleries = $this->galleries->listing($page->lang, [ (int)$page->content['tags'] ], $p, $l);
        return $res->setBody(
            $this->views->render(
                'galleries',
                array_merge($this->viewParams($page), $galleries, [ 'perpage' => $l, 'currentPage' => $p ])
            )
        );
    }
}
