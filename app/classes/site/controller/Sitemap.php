<?php

namespace site\controller;

use vakata\http\Uri as Url;
use vakata\http\Request;
use vakata\http\Response;
use League\Plates\Engine as Views;
use site\Page as PageI;
use site\data\SitemapService;
use site\Intl;
use site\data\BannersService;

class Sitemap extends Page
{
    protected $sitemap;

    public function __construct(Views $views, BannersService $banners, SitemapService $sitemap)
    {
        parent::__construct($views, $banners);
        $this->sitemap = $sitemap;
    }
    public function __invoke(PageI $page, Url $url, Request $req, Response $res): Response
    {
        return $res->setBody(
            $this->views->render(
                'sitemap',
                array_merge(
                    $this->viewParams($page),
                    $this->sitemap->get((int) $page->lang)
                )
            )
        );
    }
}
