<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Categorie;
use App\Entity\Commentaire;
use App\Entity\Emoji;
use App\Entity\Utilisateur;
use App\Form\ArticleType;
use App\Form\CommentaireType;
use App\Entity\Favori;
use App\Entity\Reaction;
use App\Repository\ArticleRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;




class ArticleController extends AbstractController
{
    private $logger;
    private $security;
    private SluggerInterface $slugger;

    public function __construct(LoggerInterface $logger, Security $security, SluggerInterface $slugger)
    {
        $this->logger = $logger;
        $this->security = $security;
        $this->slugger = $slugger;
    }



    #[Route('/article/new', name: 'article_new')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_SUPER_ADMIN');

        $article = new Article();
        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('image')->getData(); // Assume there's a field in the form named 'image'
            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                // This is needed to safely include the file name as part of the URL
                $safeFilename = $this->slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$imageFile->guessExtension();

                try {
                    $imageFile->move(
                        $this->getParameter('articles_directory'), // Define this parameter in your services.yaml
                        $newFilename
                    );
                    $article->setImage($newFilename); // Ensure your Article entity has an imageName field
                } catch (FileException $e) {
                    // Unable to upload the photo, handle exception
                    $this->addFlash('danger', 'Could not save the new file.');
                }
            }

            $entityManager->persist($article);
            $entityManager->flush();

            $this->addFlash('success', 'Article created successfully.');
            return $this->redirectToRoute('article_show', ['id' => $article->getId()]);
        }

        return $this->render('article/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/categorie/new', name: 'categorie_new', methods: ['POST'])]
    public function newCategorie(Request $request, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_SUPER_ADMIN');

        $nomCategorie = $request->request->get('nom'); // Le nom de la nouvelle catégorie passée par AJAX
        $categorie = new Categorie();
        $categorie->setNom($nomCategorie);

        $entityManager->persist($categorie);
        $entityManager->flush();

        return new JsonResponse([
            'id' => $categorie->getId(),
            'nom' => $categorie->getNom(),
        ]);
    }





    #[Route('/articles', name: 'article_index')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $articles = $entityManager->getRepository(Article::class)->findAll();
        return $this->render('article/index.html.twig', [
            'articles' => $articles,
        ]);
    }


    #[Route('/article/{id}', name: 'article_show')]
    public function show(int $id, Request $request, EntityManagerInterface $entityManager): Response
    {
        $article = $entityManager->getRepository(Article::class)->find($id);

        if (!$article) {
            throw $this->createNotFoundException('Article not found');
        }

        $comment = new Commentaire();
        $comment->setArticle($article);
        $commentForm = $this->createForm(CommentaireType::class, $comment, [
            'article' => $article,
        ]);
        $commentForm->handleRequest($request);

        if ($commentForm->isSubmitted() && $commentForm->isValid()) {
            $comment->setUtilisateur($this->getUser());

            $commentParent = $commentForm->get('commentaire_parent')->getData();
            if ($commentParent) {
                $comment->setCommentaireParent($commentParent);
            }
            $entityManager->persist($comment);
            $entityManager->flush();

            $this->addFlash('success', 'Votre commentaire a été ajouté avec succès.');

            return $this->redirectToRoute('article_show', ['id' => $article->getId()]);
        }

        return $this->render('article/show.html.twig', [
            'article' => $article,
            'comment_form' => $commentForm->createView(),
        ]);
    }







    #[Route('/article/{id}/toggle-favori', name: 'article_toggle_favori')]
    public function toggleFavori(Article $article, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();

        if (!$user instanceof Utilisateur) {
            // Si ce n'est pas le cas, récupère l'entité Utilisateur associée
            $user = $entityManager->getRepository(Utilisateur::class)->find($user->getUserIdentifier());
        }

        $favori = $entityManager->getRepository(Favori::class)->findOneBy([
            'utilisateur' => $user,
            'article' => $article,
        ]);

        if ($favori) {
            $entityManager->remove($favori);
            $this->addFlash('success', 'L\'article a été retiré de vos favoris.');
        } else
        {
            $favori = new Favori();
            $favori->setUtilisateur($this->getUser());
            $favori->setArticle($article);
            if (!$favori->getDateCreation())
            {
                $favori->setDateCreation(new \DateTimeImmutable());
            }
            $entityManager->persist($favori);
            $this->addFlash('success', 'L\'article a été ajouté à vos favoris.');
        }
        $entityManager->flush();

        return $this->redirectToRoute('article_show', ['id' => $article->getId()]);
    }


    #[Route('/articles/favoris', name: 'article_favoris')]
    public function favoris(EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();

        if (!$user) {
            $this->addFlash('error', 'Vous devez créer un compte pour accéder à vos favoris.');

            return $this->redirectToRoute('app_login');
        }

        //créer le twig pour ça
        $favoris = $entityManager->getRepository(Favori::class)->findBy(['utilisateur' => $user]);


        return $this->render('article/favoris.html.twig', [
            'favoris' => $favoris,
        ]);
    }


    #[Route('/article/reactions', name: 'article_reactions')]
    public function getReactions(EntityManagerInterface $entityManager): Response
    {
        $emojis = $entityManager->getRepository(Emoji::class)->findAll();

        return $this->render('reactions.html.twig', [
            'emojis' => $emojis,
        ]);
    }




    #[Route('/article/react/{id}', name: 'article_react')]
    public function reactToComment(int $id, Request $request, EntityManagerInterface $entityManager): Response
    {
        $commentaire = $entityManager->getRepository(Commentaire::class)->find($id);
        $user = $this->getUser();

        if (!$commentaire || !$user) {
            $this->addFlash('error', 'Commentaire non trouvé ou utilisateur non connecté.');
            return $this->redirectToRoute('article_index');
        }

        $emojiId = $request->request->get('emoji_id');

        $emoji = $entityManager->getRepository(Emoji::class)->find($emojiId);

        if (!$emoji) {
            $this->addFlash('error', 'Émoji non trouvé.');
            return $this->redirectToRoute('article_show', ['id' => $commentaire->getArticle()->getId()]);
        }

        // Création de la réaction
        $reaction = new Reaction();
        $reaction->setCommentaire($commentaire);
        $reaction->setUtilisateur($this->getUser());
        $reaction->setEmoji($emoji);

        $entityManager->persist($reaction);
        $entityManager->flush();

        $this->addFlash('success', 'Votre réaction a été ajoutée.');
        return $this->redirectToRoute('article_show', ['id' => $commentaire->getArticle()->getId()]);
    }




}
