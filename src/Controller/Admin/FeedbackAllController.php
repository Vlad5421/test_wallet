<?php

namespace App\Controller\Admin;

use App\Entity\Feedback;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class FeedbackAllController extends AbstractController
{
    /**
     * @Route("/admin/feedback/all", name = "admin_feedback_all", methods={"GET"})
     */
    public function index(Request $request, EntityManagerInterface $em)
    {
        $user_email = $request->get('user_email');

        $repo = $em->getRepository(Feedback::class);
        $messages = $repo->findBy(['userEmail' => $user_email]);

        return $this->render("feedback_all.html.twig", [
            'messages' => $messages,
            'userEmail' => $user_email
        ]);
    }
}
