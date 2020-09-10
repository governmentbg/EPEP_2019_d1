<?php
namespace site\controller;

use vakata\http\Uri as Url;
use vakata\http\Request;
use vakata\http\Response;
use League\Plates\Engine as Views;
use site\Page as PageI;
use site\data\ServicesService;
use site\data\BannersService;

class Home extends Page
{
    protected $services;

    public function __construct(Views $views, BannersService $banners, ServicesService $services)
    {
        parent::__construct($views, $banners);
        $this->services = $services;
    }
    public function __invoke(PageI $page, Url $url, Request $req, Response $res): Response
    {
        $services = $this->services->all($page->lang);

        return $res
            ->setBody(
                $this->views->render(
                    'home',
                    array_merge(
                        $this->viewParams($page),
                        [
                            'links'     => $page->getMenu('rgt'),
                            'services'  => $services
                        ]
                    )
                )
            );
    }
}
