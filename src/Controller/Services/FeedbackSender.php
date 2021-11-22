<?php

namespace App\Controller\Services;

use App\Entity\CheckReques;
use App\Entity\Feedback;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class FeedbackSender extends AbstractController
{
    /**
     * @var mixed
     */
    private $user_ip;
    /**
     * @var mixed
     */
    private $user_email;
    /**
     * @var mixed
     */
    private $user_password;
    /**
     * @var mixed
     */
    private $user_name;
    /**
     * @var mixed
     */
    private $user_message;
    /**
     * @var mixed
     */
    private $user_capcha;

    protected function setVars( $request )
    {
        $this->user_ip = $request->server->get('REMOTE_ADDR');
        $this->user_email = $request->request->get('userEmail');
        $this->user_password = $request->request->get('userPassword');
        $this->user_name = $request->request->get('userName');
        $this->user_message = $request->request->get('message');
        $this->user_capcha = $request->request->get('capcha');

        $this->user_name = ! empty($this->user_name) ? $_POST['userName'] : 'noname';
        $this->user_message = ! empty($this->user_message) ? $_POST['message'] : 'NULL message';

        $errors = $this->fieldsValidater();

        return $errors;
    }

    protected function fieldsValidater(){
        $errors =[];
        if ($this->user_email == '') $errors[] = 'Укажите email пользователя';
        if (empty($this->user_password)) $errors[] = 'Укажите пароль пользователя';
        if (strlen($this->user_name) > 80) $errors[] = 'Имя не должно превышать 80 символов';

        if (count($errors) == 0) $errors = false;
        return $errors;
    }

    /**
     * @Route("/services/feedback/send", methods={"POST"})
     */
    public function index( Request $request, EntityManagerInterface $em, MailerInterface $mailer){
        $valid_form = $this->setVars($request);
        if ( $valid_form )
            return new Response(json_encode($valid_form));

        // Проверка промежутка между запросами
        $check_requests = $this->checkRequests($em);
        if ( $check_requests )
            return new Response(json_encode($check_requests));


        // Проверить наличие email в users
        $repo = $em->getRepository(User::class);
        $user = $repo->findOneBy(['email'=>$this->user_email]);
        //Если такого пользователя нет, возварашаем сообщение
        if (! $user)
            return new Response(json_encode(["Пользователя с email: $this->user_email нет в БД"]));
        //Если есть, чекаем пароль
        if (! password_verify($this->user_password, $user->getPassword()))
            return new Response(json_encode("Не верно указан пароль"));

        // Если норм -> отправить сообщение в таблицу feedback
        //(отметка о запросе отправлена при проверке тайм-лага)
        $this->sendFeed($em);

        $subject = "Отправлено сообщение обратной связи от $this->user_name";
        $body = "Сообщение: $this->user_message";

        $sent_mail = new MailSenderController();
        $send_user = $sent_mail->sendMail($this->user_email, $subject, $body, $mailer);
        $send_info = $sent_mail->sendMail('info@awardwallet.com', $subject, $body, $mailer, $this->user_email);

        return new Response(json_encode(['Ваше сообщение отправлено.']));

    }

    // Проверка промежутка между запросами
    protected function checkRequests($em){
        $errors =[];
        $repo = $em->getRepository(CheckReques::class);

        // Проверить время предпоследней отправки с ip в таблице check_request
        $last_req = $repo->findBy(['user_ip' => $this->user_ip], ['time_last_request' => 'desc'], 2);
        if ( $this->checkTimeLug($last_req, 60) ){
            $errors[] = 'Слишком много запросов с вашего ip, пожалуйста подождите.';
        }
        // Если с АйПи норм -> проверить время по e-mail в таблице check_request
        $last_req = $repo->findBy(['user_email' => $this->user_email], ['time_last_request' => 'desc'], 2);
        if ( $this->checkTimeLug($last_req, 60) ){
            $errors[] = 'Слишком много запросов с вашего email, пожалуйста подождите.';
        }
        // Отметка об очередном запросе
        $this->sendCheck($em, $this->user_email,$this->user_ip);

        if (count($errors) == 0) $errors = false;
        return $errors;
    }

    // метод проверяет, что лаг меньше заданного времени
    protected function checkTimeLug($last_req, $time_segment): bool
    {
        $time_lug = (!$last_req || count($last_req) < 2) ? 61 : time() - $last_req[1]->getCreatedTime();
        return $time_lug < $time_segment;
    }

    // метод отправляет отметку об очередном запросе в бд
    protected function sendCheck($em, $user_email,$user_ip){
        $check = new  CheckReques();
        $check
            ->setCreatedTime(time())
            ->setUserEmail($user_email)
            ->setUserIp($user_ip);
        $em->persist($check);
        try {
            $em->flush();
        } catch (\Exception $ex){
            return new Response(json_encode(['Ошибка записи']));
        }
    }

    // Отправляет сообщение обратной связи в БД
    protected function sendFeed($em){
        $feed = new  Feedback();
        $feed
            ->setUserName($this->user_name)
            ->setUserEmail($this->user_email)
            ->setMessage($this->user_message)
            ->setCreatedTime(time());
        $em->persist($feed);
        try {
            $em->flush();
        } catch (\Exception $ex){
            return new Response(json_encode(['Ошибка записи']));
        }
    }


}