<?php
namespace site\controller;

use vakata\http\Uri as Url;
use vakata\http\Request;
use vakata\http\Response;
use League\Plates\Engine as Views;
use site\data\PollsService;
use site\Page as PageI;
use site\PageNotFoundException;
use site\ErrorException;
use site\data\BannersService;

class Polls extends Page
{
    protected $polls;

    public function __construct(Views $views, BannersService $banners, PollsService $polls)
    {
        parent::__construct($views, $banners);
        $this->polls = $polls;
    }
    public function __invoke(PageI $page, Url $url, Request $req, Response $res): Response
    {
        if ($url->getSegment(2) !== null) {
            if (!(int)$url->getSegment(2)) {
                throw new PageNotFoundException();
            }
            $poll = $this->polls->single((int) $page->lang, (int)$url->getSegment(2));
            if ($poll === null) {
                throw new PageNotFoundException();
            }
            $isPost = false;
            $errors = [];
            if ($req->getMethod() === 'POST') {
                $isPost = true;
                $data = $req->getPost();
                try {
                    $this->polls->vote($poll, $data);
                } catch (ErrorException $e) {
                    $errors = array_filter(array_merge($e->getErrors(), [ $e->getMessage() ]));
                }
                if (!count($errors)) {
                    return $res->setBody(
                        $this->views->render(
                            'pollsuccess',
                            $this->viewParams($page)
                        )
                    );
                }
            }
            if ($poll->status === 'processed') {
                return $res->setBody(
                    $this->views->render(
                        'pollresults',
                        array_merge(
                            $this->viewParams($page),
                            [ 'poll' => $poll, 'results' => $this->polls->getAnswers($poll->poll, $poll->questions) ]
                        )
                    )
                );
            } else {
                return $res->setBody(
                    $this->views->render(
                        'poll',
                        array_merge(
                            $this->viewParams($page),
                            [ 'poll' => $poll, 'isPost' => $isPost, 'errors' => $errors ?? [], 'data' => $data ?? [] ]
                        )
                    )
                );
            }
        }

        // render the listing
        $p = (int)max(1, $req->getQuery('p', 1, 'int'));
        $l = isset($page->content['perpage']) && (int)$page->content['perpage'] ? (int)$page->content['perpage'] : 10;
        $polls = $this->polls->listing($page->lang, $p, $l);
        return $res->setBody(
            $this->views->render(
                'pollslist',
                array_merge($this->viewParams($page), $polls, [ 'perpage' => $l, 'currentPage' => $p ])
            )
        );
    }
}
