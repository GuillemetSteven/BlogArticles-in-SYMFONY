<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Utilisateur;
use App\Form\ArticleType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\String\Slugger\SluggerInterface;

class AdminController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private AuthorizationCheckerInterface $authorizationChecker;
    private $slugger;



    public function __construct(EntityManagerInterface $entityManager, AuthorizationCheckerInterface $authorizationChecker, SluggerInterface $slugger)
    {
        $this->entityManager = $entityManager;
        $this->authorizationChecker = $authorizationChecker;
        $this->slugger = $slugger;
    }



    #[Route('/admin', name: 'admin_index')]
    public function index(): Response
    {
        if (!$this->authorizationChecker->isGranted('ROLE_SUPER_ADMIN')) {
            throw $this->createAccessDeniedException();
        }

        $users = $this->entityManager->getRepository(Utilisateur::class)->findAll();
        $articles = $this->entityManager->getRepository(Article::class)->findAll();

        return $this->render('admin/index.html.twig', [
            'users' => $users,
            'articles' => $articles,
        ]);

    }

    #[Route('/delete/{id}', name: 'admin_delete_user', methods: ['POST'])]
    public function deleteUser(Utilisateur $user, Request $request): Response
    {
        if (!$this->authorizationChecker->isGranted('ROLE_SUPER_ADMIN')) {
            throw $this->createAccessDeniedException('Seul un super administrateur peut effectuer cette action.');
        }

        if ($this->isCsrfTokenValid('delete' . $user->getId(), $request->request->get('_token'))) {
            $this->entityManager->remove($user);
            $this->entityManager->flush();
            $this->addFlash('success', 'Utilisateur supprimé avec succès.');
        } else {
            $this->addFlash('error', 'Jeton de sécurité invalide.');
        }

        return $this->redirectToRoute('admin_index');
    }



    #[Route('/promote/{id}', name: 'promote_to_admin')]
    public function promoteToAdmin(int $id): Response
    {
        $user = $this->entityManager->getRepository(Utilisateur::class)->find($id);
        if (!$user) {
            throw $this->createNotFoundException('Utilisateur non trouvé');
        }

        // Assurez-vous que l'utilisateur n'est pas déjà un admin
        if (!in_array('ROLE_SUPER_ADMIN', $user->getRoles())) {
            $user->addRole('ROLE_SUPER_ADMIN');
            $this->entityManager->flush();
            $this->addFlash('success', 'L\'utilisateur a été promu administrateur.');
        } else {
            $this->addFlash('error', 'L\'utilisateur est déjà administrateur.');
        }

        return $this->redirectToRoute('admin_index');
    }


    #[Route('/demote/{id}', name: 'demote_from_admin')]
    public function demoteFromAdmin(int $id): Response
    {
        if (!$this->authorizationChecker->isGranted('ROLE_SUPER_ADMIN')) {
            throw $this->createAccessDeniedException();
        }


        $user = $this->entityManager->getRepository(Utilisateur::class)->find($id);
        if (!$user) {
            throw $this->createNotFoundException('Utilisateur non trouvé.');
        }

        if (in_array('ROLE_SUPER_ADMIN', $user->getRoles())) {
            $user->removeRole('ROLE_SUPER_ADMIN');
            $this->entityManager->flush();
            $this->addFlash('success', 'L\'utilisateur a été rétrogradé avec succès.');
        } else {
            $this->addFlash('error', 'L\'utilisateur n\'est pas administrateur.');
        }

        return $this->redirectToRoute('admin_index');
    }



    #[Route('/admin/articles', name: 'admin_articles')]
    public function articles(): Response
    {
        $this->denyAccessUnlessGranted('ROLE_SUPER_ADMIN');

        $articles = $this->entityManager->getRepository(Article::class)->findAll();

        return $this->render('admin/articles.html.twig', [
            'articles' => $articles,
        ]);
    }

    #[Route('/admin/article/delete/{id}', name: 'admin_article_delete', methods: ['POST'])]
    public function deleteArticle(Article $article, Request $request, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$article->getId(), $request->request->get('_token'))) {
            // Dissocier ou supprimer toutes les dépendances
            foreach ($article->getCommentaires() as $commentaire) {
                // Dissociez les commentaires des articles
                $commentaire->setArticle(null);
                $entityManager->remove($commentaire); // ou laissez-les si vous voulez les garder
            }

            $entityManager->remove($article);
            $entityManager->flush();
            $this->addFlash('success', 'Article supprimé avec succès.');
        } else {
            $this->addFlash('error', 'Jeton CSRF invalide.');
        }

        return $this->redirectToRoute('admin_index'); // Assurez-vous que cette redirection est correcte
    }


    #[Route('/article/edit/{id}', name: 'article_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, EntityManagerInterface $entityManager, int $id): Response
    {
        $this->denyAccessUnlessGranted('ROLE_SUPER_ADMIN');

        $article = $entityManager->getRepository(Article::class)->find($id);
        if (!$article) {
            throw $this->createNotFoundException('No article found for id '.$id);
        }

        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('image')->getData();
            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $this->slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$imageFile->guessExtension();

                try {
                    $imageFile->move(
                        $this->getParameter('articles_directory'),
                        $newFilename
                    );
                    $article->setImage($newFilename);
                } catch (FileException $e) {
                    $this->addFlash('danger', 'Could not upload the image.');
                }
            }

            $entityManager->flush();
            $this->addFlash('success', 'Article updated successfully.');

            return $this->redirectToRoute('article_show', ['id' => $article->getId()]);
        }

        return $this->render('article/edit.html.twig', [
            'article' => $article,
            'form' => $form->createView(),
        ]);
    }







}
