<?php

namespace site\controller;

use vakata\http\Uri as Url;
use vakata\http\Request;
use vakata\http\Response;
use League\Plates\Engine as Views;
use site\Page as PageI;
use site\PageNotFoundException;
use site\data\NewsService;
use site\data\BannersService;

class News extends Page
{
    protected $news;

    public function __construct(Views $views, BannersService $banners, NewsService $news)
    {
        parent::__construct($views, $banners);
        $this->news = $news;
    }
    public function __invoke(PageI $page, Url $url, Request $req, Response $res): Response
    {
        // render a single news item
        if ($url->getSegment(2) !== null) {
            if (!(int)$url->getSegment(2)) {
                throw new PageNotFoundException();
            }
            $news = $this->news->single($page->lang, (int)$url->getSegment(2));
            if ($news === null) {
                throw new PageNotFoundException();
            }
            return $res->setBody(
                $this->views->render(
                    'news',
                    array_merge($this->viewParams($page), [ 'news' => $news ])
                )
            );
        }

        // render the listing
        $p = (int)max(1, $req->getQuery('p', 1, 'int'));
        $l = isset($page->content['perpage']) && (int)$page->content['perpage'] ? (int)$page->content['perpage'] : 10;
        $news = $this->news->listing(
            $page->lang,
            [ isset($page->content['tags']) ? (int) $page->content['tags'] : null ],
            $p,
            $l
        );
        return $res->setBody(
            $this->views->render(
                'newslist',
                array_merge($this->viewParams($page), $news, [ 'perpage' => $l, 'currentPage' => $p ])
            )
        );
    }
}
