<?php
namespace site\controller;

use vakata\http\Uri as Url;
use vakata\http\Request;
use vakata\http\Response;
use League\Plates\Engine as Views;
use site\Page as PageI;
use site\data\BannersService;

class Page
{
    protected $views;
    protected $banners;

    public function __construct(Views $views, BannersService $banners)
    {
        $this->views = $views;
        $this->banners = $banners;
    }
    protected function viewParams(PageI $page): array
    {
        $site = $page->site((int) $page->lang);

        return [
            'site'       => $site,
            'page'       => $page,
            'menu'       => $page->getTopMenu(),
            'breadcrumb' => array_map(
                function ($v) {
                    return [
                        'title' => $v->title,
                        'link'  => $v->getUrl()
                    ];
                },
                $page->getBreadcrumb()
            ),
            'children'   => array_filter(
                $page->getChildren(false),
                function ($v) {
                    return (int)$v->menu > 0;
                }
            ),
            'translations'  => $page->getTranslations(),
            'footer'        => [
                'footer'    => $page->footer(),
                'banners'   => $this->banners->all((int) $page->lang),
                'site'      => $site
            ]
        ];
    }
    public function __invoke(PageI $page, Url $url, Request $req, Response $res): Response
    {
        return $res->setBody($this->views->render('page', $this->viewParams($page)));
    }
}
