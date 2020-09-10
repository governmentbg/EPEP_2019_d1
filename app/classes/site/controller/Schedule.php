<?php
namespace site\controller;

use vakata\http\Uri as Url;
use vakata\http\Request;
use vakata\http\Response;
use League\Plates\Engine as Views;
use site\data\ScheduleService;
use site\Page as PageI;
use site\data\BannersService;

class Schedule extends Page
{
    protected $schedule;

    public function __construct(Views $views, BannersService $banners, ScheduleService $schedule)
    {
        parent::__construct($views, $banners);
        $this->schedule = $schedule;
    }
    public function __invoke(PageI $page, Url $url, Request $req, Response $res): Response
    {
        $params = $req->getQuery();

        if (count($params)) {
            $code = $page->site($page->lang)['code'] ?? null;
            try {
                $data = $this->schedule->request(array_merge($params, [ 'courtCode' => $code ]));
            } catch (\Exception $e) {
                $error = $e->getMessage();
            }
        }
        $plaintiffs = [
            'Възложител', 'Обвинител', 'Ищец', 'Молител', 'Заявител', 'Жалбоподател', 'Жалбоподател-подсъдим',
            'Жалбоподател-гр.ищец', 'Жалбоподател-ч.обвинител', 'Жалбоподател-ответник', 'Протестираща страна',
            'Въззивник', 'Тъжител', 'Собственик', 'Кредитор', 'Съдружник', 'Управител', 'Прокурист',
            'Търговски представител', 'Член на УС', 'Член на СД', 'Касатор', 'Пастор', 'Епископ', 'Ахриепископ',
            'Пастир', 'Член на Духовен съвет', 'Председател', 'Член на Съвет на старейшините',
            'Член на Епархийски съвет', 'Изпълнителен директор', 'Член на Брахмана съвет', 'Синдик', 'Помощник-синдик',
            'Ликвидатор', 'Частен обвинител', 'Особен представител', 'Жалбоподател - Осъдено лице', 'Председател на ПС',
            'Частен жалбоподател', 'Частен жалбоподател-длъжник', 'Частен жалбоподател-заявител', 'Частен жалбоподател',
            'Вносител', 'Админ.наказ.орган'
        ];
        $defendants = [
            'Подсъдим', 'Ответник', 'Насрещна страна', 'Нарушител', 'Уличено лице', 'Освидетелстван','Длъжник',
            'Обвиняем', 'Осъден', 'Лице по принудително действие', 'Граждански ответник', 'Искано лице',
            'Солидарен длъжник', 'Въззиваема страна'
        ];

        return $res->setBody(
            $this->views->render(
                'schedule',
                array_merge(
                    $this->viewParams($page),
                    [
                        'hearingTypes'  => $this->schedule->getHearingTypes(),
                        'caseTypes'     => $this->schedule->getCaseTypes(),
                        'data'          => $data ?? null,
                        'params'        => $params,
                        'error'         => $error ?? null,
                        'plaintiffs'    => $plaintiffs,
                        'defendants'    => $defendants
                    ]
                )
            )
        );
    }
}
