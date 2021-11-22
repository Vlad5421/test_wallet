<?php

namespace App\Controller\Services;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;

class MailSenderController extends AbstractController
{

    public function sendMail($to, $subject, $body, $mailer)
    {
        $result = true;

        $to = 'vladislav_ts@bk.ru'; // для проверки работоспособности зафиксировал почту
        $email = new Email();
        $email
            ->from('vladislav_ts@list.ru')
            ->to($to)
            ->subject($subject)
            ->html($body);
//        $mailer = new MailerInterface();
        try {
            $mailer->send($email);
        } catch (\Exception $ex) {
            $result = false;
        }

        return $result;
    }
}
