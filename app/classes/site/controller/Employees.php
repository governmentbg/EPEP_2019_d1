<?php
namespace site\controller;

use vakata\http\Uri as Url;
use vakata\http\Request;
use vakata\http\Response;
use League\Plates\Engine as Views;
use site\data\EmployeesService;
use site\Page as PageI;
use site\data\BannersService;

class Employees extends Page
{
    protected $contacts;

    public function __construct(Views $views, BannersService $banners, EmployeesService $contacts)
    {
        parent::__construct($views, $banners);
        $this->contacts = $contacts;
    }
    public function __invoke(PageI $page, Url $url, Request $req, Response $res): Response
    {
        $employees = array_map(function ($item) {
            return ((int) $item['employee']) ?? null;
        }, json_decode($page->content['employees'], true) ?? []);

        $employees = array_filter($employees);

        return $res->setBody(
            $this->views->render(
                'employees',
                array_merge(
                    $this->viewParams($page),
                    [
                        'employees'     => $this->contacts->employees($employees)
                    ]
                )
            )
        );
    }
}
