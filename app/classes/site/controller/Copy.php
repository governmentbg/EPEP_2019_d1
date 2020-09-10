<?php
namespace site\controller;

use vakata\http\Uri as Url;
use vakata\http\Request;
use vakata\http\Response;
use League\Plates\Engine as Views;
use site\Page as PageI;
use site\data\BannersService;
use site\data\CopyService;
use vakata\di\DIContainer;
use site\PageNotFoundException;

class Copy extends Page
{
    protected $di = null;
    protected $copy = null;

    public function __construct(Views $views, BannersService $banners, CopyService $copy, DIContainer $di)
    {
        parent::__construct($views, $banners);
        $this->di = $di;
        $this->copy = $copy;
    }
    public function __invoke(PageI $page, Url $url, Request $req, Response $res): Response
    {
        if (isset($page->content['node']) && (int) $page->content['node']) {
            $template = $this->copy->template((int) $page->content['node'], (int) $page->lang);
            if ($template && isset(CLASSES[$template])) {
                $controller = $this->di->instance(CLASSES[$template]);
                return $controller($page, $url, $req, new Response());
            }
        }
        throw new PageNotFoundException();
    }
}
