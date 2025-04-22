<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Commentaire;
use App\Form\ArticleType;
use App\Form\CommentaireType;
use App\Repository\ArticleRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/article')]
class ArticleController extends AbstractController
{
    #[Route('/', name: 'app_article_index', methods: ['GET'])]
    public function index(
        Request $request,
        ArticleRepository $articleRepository,
        UserRepository $userRepository
    ): Response {
        $title    = $request->query->get('title');
        $authorId = $request->query->get('author');
        $author   = $authorId ? $userRepository->find($authorId) : null;

        $articles = $articleRepository->findByFilters($title, $author);
        $authors  = $userRepository->findAuthors();

        return $this->render('article/index.html.twig', [
            'articles'      => $articles,
            'authors'       => $authors,
            'search_title'  => $title,
            'search_author' => $authorId,
        ]);
    }

    #[Route('/new', name: 'app_article_new', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $article = new Article();
        $article->setAuteur($this->getUser());

        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($article);
            $em->flush();

            return $this->redirectToRoute('app_article_index');
        }

        return $this->renderForm('article/new.html.twig', [
            'article' => $article,
            'form'    => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_article_show', methods: ['GET','POST'])]
    public function show(
        Article $article,
        Request $request,
        EntityManagerInterface $em
    ): Response {
        // gestion des commentaires en POST sur /article/{id}
        $commentaire = new Commentaire();
        $form = $this->createForm(CommentaireType::class, $commentaire);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if (! $this->getUser()) {
                throw $this->createAccessDeniedException('Vous devez être connecté pour commenter.');
            }
            if ($form->isValid()) {
                $commentaire->setUtilisateur($this->getUser());
                $commentaire->setArticle($article);
                $commentaire->setCreatedAt(new \DateTimeImmutable());
                $em->persist($commentaire);
                $em->flush();

                return $this->redirectToRoute('app_article_show', ['id' => $article->getId()]);
            }
        }

        return $this->render('article/show.html.twig', [
            'article'      => $article,
            'commentaires' => $article->getCommentaires(),
            'formComment'  => $form->createView(),
        ]);
    }

    #[Route('/{id}/edit', name: 'app_article_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Article $article, EntityManagerInterface $em): Response
    {
        if ($this->getUser() !== $article->getAuteur() && ! $this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException('Vous ne pouvez pas modifier cet article.');
        }

        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            return $this->redirectToRoute('app_article_index');
        }

        return $this->renderForm('article/edit.html.twig', [
            'article' => $article,
            'form'    => $form,
        ]);
    }

    // ← CHANGEMENT : on passe en /{id}/delete
    #[Route('/{id}/delete', name: 'app_article_delete', methods: ['POST'])]
    public function delete(Request $request, Article $article, EntityManagerInterface $em): Response
    {
        if ($this->getUser() !== $article->getAuteur() && ! $this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException('Vous ne pouvez pas supprimer cet article.');
        }

        if ($this->isCsrfTokenValid('delete' . $article->getId(), $request->request->get('_token'))) {
            $em->remove($article);
            $em->flush();
            $this->addFlash('success', 'Article supprimé.');
        }

        return $this->redirectToRoute('app_article_index');
    }
}
