<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Categorie;
use App\Repository\ArticleRepository;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'home_index')]
    public function index(EntityManagerInterface $entityManager, Request $request): Response
    {
        $categoryId = $request->query->get('category');
        if ($categoryId) {
            $articles = $entityManager->getRepository(Article::class)->findBy(['categorie' => $categoryId]);
        } else {
            $articles = $entityManager->getRepository(Article::class)->findAll();
        }
        $categories = $entityManager->getRepository(Categorie::class)->findAll();

        return $this->render('home/index.html.twig', [
            'articles' => $articles,
            'categories' => $categories,
            'selectedCategoryId' => $categoryId,
        ]);
    }


}
