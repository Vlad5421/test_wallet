<?php

namespace App\Controller\Services;

use App\Entity\CheckReques;
use App\Entity\Feedback;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Response;
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

    protected function varSetter()
    {

    }

    /**
     * @Route("/services/feedback/send")
     */
    public function index(LoggerInterface $logger, EntityManagerInterface $em){
        $user_ip = $_SERVER['REMOTE_ADDR'];
        if (!empty($_POST['userEmail']))
            $user_email = $_POST['userEmail'];
        else
            return new Response(json_encode(['Укажите email пользователя']));

        if (!empty($_POST['userPassword']))
            $user_password = $_POST['userPassword'];
        else
            return new Response(json_encode(['Укажите пароль пользователя']));
        $user_name = ! empty($_POST['userName']) ? $_POST['userName'] : 'noname';
        $user_message = ! empty($_POST['message']) ? $_POST['message'] : 'NULL message';


        $repo = $em->getRepository(CheckReques::class);
        // Проверить время предпоследней отправки с ip в таблице check_request
        $last_req = $repo->findBy(['user_ip' => $user_ip], ['time_last_request' => 'desc'], 2);
        $time_lug = time() - $last_req[1]->getCreatedTime();
        if ( $this->checkTimeLug($time_lug, 60) ){
            $this->sendCheck($em, $user_email,$user_ip);
            return new Response(json_encode(['Слишком много запросов с вашего ip, пожалуйста подождите.']));
        }
        // Если с АйПи норм -> проверить время по e-mail в таблице check_request
        $last_req = $repo->findBy(['user_email' => $user_email], ['time_last_request' => 'desc'], 2);
        $time_lug = time() - $last_req[1]->getCreatedTime();

        if ( $this->checkTimeLug((int) $time_lug, 60) ){
            $this->sendCheck($em, $user_email,$user_ip);
            return new Response(json_encode(['Слишком много запросов с вашего email, пожалуйста подождите.']));
        }

        // Проверить наличие email в users
        $repo = $em->getRepository(User::class);
        $user = $repo->findOneBy(['email'=>$user_email]);
        //Если такого пользователя нет, возварашаем сообщение
        if (! $user) return new Response(json_encode(["Пользователя с email: $user_email нет в БД"]));
        //Если есть, чекаем пароль
        if (! password_verify($user_password, $user->getPassword()))
            return new Response(json_encode("Не верно указан пароль"));

        // Если норм -> записать отметку о запросе -> отправить сообщение в таблицу feedback
        $this->sendCheck($em, $user_email,$user_ip);
        $this->sendFeed($em,$user_name, $user_email, $user_message);

        return new Response(json_encode(['Ваше сообщение отправлено.']));

    }

    protected function checkTimeLug($time_lug, $time_segment){
        return $time_lug < $time_segment;
    }

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

    protected function sendFeed($em, $user_name, $user_email, $user_message){
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