<?php
namespace site\controller;

use vakata\http\Uri as Url;
use vakata\http\Request;
use vakata\http\Response;
use League\Plates\Engine as Views;
use site\data\AnnouncementsService;
use site\Page as PageI;
use site\data\BannersService;

class Announcements extends Page
{
    protected $annoucements;

    public function __construct(Views $views, BannersService $banners, AnnouncementsService $annoucements)
    {
        parent::__construct($views, $banners);
        $this->annoucements = $annoucements;
    }
    public function __invoke(PageI $page, Url $url, Request $req, Response $res): Response
    {
        $p = (int) max(1, $req->getQuery('p', 1, 'int'));
        $l = 12;
        $type = !in_array($req->getQuery('type'), [ 'properties', 'vehicles', 'assets' ]) ? 'properties' : $req->getQuery('type');

        return $res->setBody(
            $this->views->render(
                'announcements',
                array_merge(
                    $this->viewParams($page),
                    $this->annoucements->listing(
                        isset($page->content['link_' . $type]) && strlen(trim($page->content['link_' . $type])) ?
                            trim($page->content['link_' . $type]) :
                            null,
                        $p,
                        $l
                    )
                )
            )
        );
    }
}
