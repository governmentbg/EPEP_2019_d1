<?php

namespace site\data;

use vakata\database\DBInterface;
use vakata\collection\Collection;
use site\ErrorException;

class PollsService
{
    protected $db;

    public function __construct(DBInterface $db)
    {
        $this->db = $db;
    }
    public function single(int $lang, int $id)
    {
        $temp = $this->db->all(
            'SELECT
                polls.poll,
                polls.title as polltitle,
                poll_questions.question,
                poll_questions.type,
                poll_questions.title as questiontitle,
                poll_question_answers.answer,
                poll_question_answers.title as answertitle,
                polls._status as status
            FROM
                polls
            LEFT JOIN
                poll_questions ON polls.poll = poll_questions.poll
            LEFT JOIN
                poll_question_answers ON poll_question_answers.question = poll_questions.question
            WHERE
                polls.hidden = ? AND
                polls.lang = ? AND
                (
                    polls._status = ? OR
                    polls._status = ?
                ) AND
                polls.site = ? AND
                polls.poll = ? AND
                polls.visible_end > ? AND
                polls.visible_beg < ?
            ORDER BY
                poll_questions.question ASC, poll_question_answers.answer ASC',
            [ 0, $lang, 'published', 'processed', SITE, $id, date('Y-m-d H:i:s'), date('Y-m-d H:i:s') ]
        );
        if (!count($temp)) {
            return null;
        }

        $data = [];

        foreach ($temp as $value) {
            $data['poll']       = $value['poll'];
            $data['title']      = $value['polltitle'];
            $data['status']     = $value['status'];
            if (!isset($data['questions'])) {
                $data['questions'][$value['question']] = [];
            }
            $data['questions'][$value['question']]['question']  = $value['question'];
            $data['questions'][$value['question']]['title']     = $value['questiontitle'];
            $data['questions'][$value['question']]['type']      = $value['type'];

            if (!isset($data['questions'][$value['question']]['answers'])) {
                $data['questions'][$value['question']]['answers'] = [];
            }
            if ($value['type'] != 'open') {
                $data['questions'][$value['question']]['answers'][$value['answer']] = [
                    'answer'    => $value['answer'],
                    'title'     => $value['answertitle']
                ];
            }
        }

        return (object) $data;
    }
    public function listing(string $lang, int $page = 1, int $perpage = 10)
    {
        $query = $this->db->polls()
            ->filter('lang', $lang)
            ->filter('hidden', 0)
            ->filter('_status', ['published', 'processed'])
            ->filter('site', SITE)
            ->where('polls.visible_end > ? AND polls.visible_beg < ?', [ date('Y-m-d H:i:s'), date('Y-m-d H:i:s') ])
            ->paginate($page, $perpage);

        return [
            'polls' => Collection::from($query->select())
                ->map(function ($v) {
                    return (object) $v;
                })
                ->toArray(),
            'count' => $query->count()
        ];
    }
    public function vote(object $poll, array $data)
    {
        if (!isset($data['answer']) || !is_array($data['answer']) || !count($data['answer'])) {
            throw new ErrorException('polls.invalid');
        }
        $errors = [];
        foreach ($poll->questions as $question) {
            if (!isset($data['answer'][$question['question']])) {
                $errors[] = 'polls.question.required';
            }
            $answer = $data['answer'][$question['question']];
            switch ($question['type']) {
                case 'checkbox':
                    if (!is_array($answer) || !count($answer)) {
                        $errors[] = 'polls.question.required';
                        break;
                    }
                    $answers = [];
                    foreach ($question['answers'] as $ans) {
                        if (in_array($ans['answer'], $answer)) {
                            $answers[$ans['answer']] = $ans['answer'];
                        }
                    }
                    if (!count($answers)) {
                        $errors[] = 'polls.question.required';
                    } else {
                        $data['answer'][$question['question']] = $answers;
                    }
                    break;
                case 'radio':
                    if (!(int) $answer || !isset($question['answers'][$answer])) {
                        $errors[] = 'polls.question.required';
                    }
                    break;
                case 'open':
                    if (!strlen(trim($answer))) {
                        $errors[] = 'polls.question.required';
                    }
                    break;
                default:
                    break;
            }
        }
        if (count($errors)) {
            throw (new ErrorException())->setErrors(array_unique($errors));
        }
        $this->db->query(
            'INSERT INTO poll_answers (poll, answers) VALUES (?, ?)',
            [ $poll->poll, json_encode($data['answer']) ]
        );
    }
    public function getAnswers(int $id, $data)
    {
        $answers = $this->db->all('SELECT answers FROM poll_answers WHERE poll = ?', [ $id ]);

        $results = [];
        foreach ($data as $question => $value) {
            if (count($value['answers'])) {
                foreach ($value['answers'] as $answer) {
                    $results[$question][$answer['answer']] = 0;
                }
            } else {
                $results[$question] = [];
            }
        }
        foreach ($answers as $value) {
            $value = json_decode($value, true) ?? [];
            foreach ($value as $question => $answer) {
                if (is_array($answer)) {
                    foreach ($answer as $val) {
                        if (isset($results[$question][$val])) {
                            $results[$question][$val]++;
                        }
                    }
                } else {
                    if ($data[$question]['type'] == 'open') {
                        $results[$question][] = $answer;
                    } else {
                        $results[$question][$answer]++;
                    }
                }
            }
        }

        return [ 'count' => count($answers), 'results' => $results ];
    }
}
