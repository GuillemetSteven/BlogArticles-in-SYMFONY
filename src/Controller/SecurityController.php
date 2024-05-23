<?php

namespace App\Controller;

use App\Entity\Utilisateur;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;


class SecurityController extends AbstractController
{

    #[Route(path: '/login', name: 'app_login')]
    public function loginCheck(Request $request, AuthenticationUtils $authenticationUtils, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
    {
        if ($this->getUser()) {
            if ($this->isGranted('ROLE_SUPER_ADMIN')) {
                return $this->redirectToRoute('admin_index');
            } else {
                return $this->redirectToRoute('home');
            }
        }



        $lastUsername = $authenticationUtils->getLastUsername();
        $user = $entityManager->getRepository(Utilisateur::class)->findOneBy(['email' => $lastUsername]);

        if ($user) {
            $password = $request->get('password');

            if ($password !== null && $passwordHasher->isPasswordValid($user, $password)) {
                if (in_array('ROLE_SUPER_ADMIN', $user->getRoles())) {
                    return $this->redirectToRoute('admin_index');
                } else {
                    return $this->redirectToRoute('home');
                }
            }
        }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
