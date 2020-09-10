<?php
namespace site\controller;

use vakata\http\Uri as Url;
use vakata\http\Request;
use vakata\http\Response;
use League\Plates\Engine as Views;
use site\data\ContactsService;
use site\Page as PageI;
use site\data\BannersService;

class Contacts extends Page
{
    protected $contacts;

    public function __construct(Views $views, BannersService $banners, ContactsService $contacts)
    {
        parent::__construct($views, $banners);
        $this->contacts = $contacts;
    }
    public function __invoke(PageI $page, Url $url, Request $req, Response $res): Response
    {
        $departments = array_map(function ($item) {
            return ((int) $item['department']) ?? null;
        }, json_decode($page->content['departments'], true) ?? []);

        $departments = $this->contacts->departments(array_filter($departments));

        return $res->setBody(
            $this->views->render(
                'contacts',
                array_merge(
                    $this->viewParams($page),
                    [
                        'departments'   => $departments,
                        'employees'     => $this->contacts->employees($departments)
                    ]
                )
            )
        );
    }
}
