<?php

namespace App\Controller\Services;

use Symfony\Bridge\Twig\Mime\TemplatedEmail;

class MailSenderController
{

    public function sendMail($to, $subject, $body, $mailer, string $sending_user = '')
    {
        $result = true;
        $context = [];

        if ($sending_user !== ''){
            $context['userEmail'] = $sending_user;
            $body = "$body. Все сообщения пользователя: ";
        }
        $context = array_merge($context, ['body' => $body, 'subject' => $subject]);
        $to = 'vladislav_ts@bk.ru'; // для проверки работоспособности зафиксировал почту
        $email = new TemplatedEmail();
        $email
            ->from('vladislav_ts@list.ru')
            ->to($to)
            ->subject($subject)
            ->htmlTemplate('mail_template.html.twig')
            ->context($context);

        try {
            $mailer->send($email);
        } catch (\Exception $ex) {
            $result = false;
        }

        return $result;
    }
}
