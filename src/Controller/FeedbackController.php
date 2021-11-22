<?php

namespace App\Controller;


use App\Controller\Services\CapchaService;
use App\Controller\Services\FeedbackSender;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class FeedbackController extends AbstractController
{
    /**
     * @Route("/feedback", name="app_feedback")
     */
    public function sendFeedBack(FeedbackSender $sender)
    {
        $capcha = new CapchaService();
        $cap_vars = $capcha->makeCapcha();

        return $this->render('feedback_form.html.twig', [
            'capcha1' => $cap_vars['capcha1'],
            'capcha2' => $cap_vars['capcha2'],
        ]);
    }

}