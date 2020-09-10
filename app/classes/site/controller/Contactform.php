<?php
namespace site\controller;

use vakata\http\Uri as Url;
use vakata\http\Request;
use vakata\http\Response;
use League\Plates\Engine as Views;
use site\data\ContactsService;
use site\Page as PageI;
use site\ErrorException;
use vakata\jwt\JWT;
use vakata\jwt\TokenException;
use site\Intl;
use vakata\mail\driver\SMTPSender;
use vakata\mail\Mail;
use site\data\BannersService;

class Contactform extends Page
{
    protected $contacts;
    protected $intl;
    protected $sender;

    public function __construct(Views $views, BannersService $banners, ContactsService $contacts, Intl $intl, SMTPSender $sender)
    {
        parent::__construct($views, $banners);
        $this->contacts = $contacts;
        $this->intl = $intl;
        $this->sender = $sender;
    }
    public function __invoke(PageI $page, Url $url, Request $req, Response $res): Response
    {
        if ($req->getMethod() === 'POST') {
            $isPost = true;
            try {
                $signal = $this->contacts->signal(array_merge($req->getPost(), [ 'lang' => $page->lang ]), $req->getUploadedFiles()['file'] ?? null);
                $token = (new JWT([
                    'iss'       => APPNAME,
                    'ip'        => $req->getAttribute('client-ip'),
                    'sess'      => session_id(),
                    'ua'        => $req->getHeaderLine('User-Agent'),
                    'mail'      => $req->getPost('mail'),
                    'signal'    => (int) $signal
                ], 'HS256'))
                    ->setExpiration(time() + (24 * 3600))
                    ->sign(SIGNATUREKEY)
                    ->toString();
                $message = '<p>Г-н/Г-жо ' . htmlspecialchars($req->getPost('name')) . '</p>' . 
                    '<p>За да обработим сигнала Ви, моля потвърдете изпращането му на следния линк <a href="' . $url->get($url->self(true), [ 'token' => $token ], 1) . '">' . $url->get($url->self(true), [ 'token' => $token ], 1) . '</a></p>' . 
                    '<p>Изпратения от Вас сигнал през Електронна кутия за предложения и сигнали на страницата ни е с текст:</p>' . 
                    '<p>' . htmlspecialchars($req->getPost('message')) . '</p><br />' .
                    '<p>Благодарим за обратната връзка!</p>';
                $mail = new Mail(MAILFROM, $this->intl->get('contactform.mail.subject'), $message);
                $mail->setTo($req->getPost('mail'));
                $this->sender->send($mail);
            }
            catch (ErrorException $e) {
                $errors = $e->getErrors();
            }

            if (!isset($errors) || !count($errors)) {
                return $res->setBody(
                    $this->views->render(
                        'contactformsuccess',
                        array_merge(
                            $this->viewParams($page)
                        )
                    )
                );
            }
        }
        elseif ($req->getQuery('token')) {
            try {
                $token = JWT::fromString($req->getQuery('token'));
                $valid = $token->isSigned() && $token->isValid() && $token->verify(SIGNATUREKEY);
                $this->contacts->validateSignal($token->getClaims());
            }
            catch (\Exception $e) {
                $valid = false;
            }

            return $res->setBody(
                $this->views->render(
                    'contactformtoken',
                    array_merge(
                        $this->viewParams($page),
                        [
                            'valid' => $valid
                        ]
                    )
                )
            );
        }
        return $res->setBody(
            $this->views->render(
                'contactform',
                array_merge(
                    $this->viewParams($page),
                    [
                        'isPost'    => $isPost ?? false,
                        'errors'    => $errors ?? [],
                        'params'    => $req->getPost()
                    ]
                )
            )
        );
    }
}
