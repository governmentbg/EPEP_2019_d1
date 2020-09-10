<?php
namespace site\controller;

use vakata\http\Uri as Url;
use vakata\http\Request;
use vakata\http\Response;
use site\Page as PageI;

class Embed extends Page
{
    public function __invoke(PageI $page, Url $url, Request $req, Response $res): Response
    {
        return $res->setBody(
            $this->views->render(
                'embed',
                array_merge(
                    $this->viewParams($page),
                    [
                        'link'  => $page->content['link'] ?? null
                    ]
                )
            )
        );
    }
}
