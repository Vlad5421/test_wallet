<?php

namespace App\Controller\Admin;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AddEmailsController extends AbstractController
{
    /**
     * @Route("/admin/add/users/{name}", name="admin_add_users")
     */
    // написал, что бы проще создавать пользователей в БД
    public function index(EntityManagerInterface $em, $name): Response
    {
        $user = new User();

        $user
            ->setEmail($name. '@mail.ru')
            ->setPassword(password_hash($name,PASSWORD_DEFAULT));

        try {
            $em->persist($user);
            $em->flush();
        } catch (\Exception $ex) {
            return new Response("Наверное пользовательс именем $name уже существует, попробуйте указать другое имя.");
        }

        return new Response(sprintf(
            'Служебная стараница для создания пользователей. Создан пользователь: %s',
            $user->getEmail()
        ));
    }
}
