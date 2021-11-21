<?php

namespace App\Controller\Services;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;

class CapchaService
{
    public function makeCapcha(){
        $session = new Session();
        $capcha1 = rand(1, 30);
        $capcha2 = rand(1, 30);
        $capcha_check = $capcha1 + $capcha2;
        $session->start();

        $session
            ->set('capcha_check', $capcha_check);
        return (['capcha1' => $capcha1, 'capcha2' => $capcha2]);
    }
    /**
     * @Route("/services/chack-capcha/{summ}")
     */
    public function checkCapcha($summ, Session $session){
        $session->start();
        $response = $session->get('capcha_check') == (int) $summ ? 1 : 0;
        return new Response(json_encode($response));
    }

}