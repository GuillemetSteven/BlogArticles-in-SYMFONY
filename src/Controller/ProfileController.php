<?php


namespace App\Controller;
use App\Entity\Utilisateur;
use App\Form\ProfileFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[Route('/profile')]
class ProfileController extends AbstractController
{

    #[Route('/profile', name: 'profile_index')]
    public function profile(): Response
    {
        $user = $this->getUser();
        if (!$user) {
            throw $this->createNotFoundException('User not found.');
        }

        return $this->render('profile/index.html.twig', [
            'user' => $user,
            'favoris' => $user->getFavoris(),
        ]);
    }



    #[Route('/edit', name: 'profile_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();

        // Make sure there's a user logged in
        if (!$user instanceof UserInterface) {
            throw $this->createNotFoundException('No user is logged in.');
        }

        $form = $this->createForm(ProfileFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if (!empty($form->get('plainPassword')->getData())) {
                $hashedPassword = $passwordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                );
                $user->setPassword($hashedPassword);
            }

            $file = $request->files->get('profile_image');
            if ($file && $file->isValid()) {
                $filename = md5(uniqid()) . '.' . $file->guessExtension();
                $file->move($this->getParameter('profile_images_directory'), $filename);
                $user->setUserProfileImage($filename);
            }

            $entityManager->flush();
            $this->addFlash('success', 'Profile updated successfully!');
            return $this->redirectToRoute('profile_index');
        }

        return $this->render('profile/edit.html.twig', [
            'form' => $form->createView()
        ]);
    }
}

