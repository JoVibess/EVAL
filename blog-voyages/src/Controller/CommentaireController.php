<?php

namespace App\Controller;

use App\Entity\Commentaire;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CommentaireController extends AbstractController
{
    #[Route('/commentaire/{id}', name: 'app_commentaire_delete', methods: ['POST'])]
    public function delete(
        Commentaire $commentaire,
        Request $request,
        EntityManagerInterface $em
    ): Response {
        // 1) Vérification de droit : l’auteur du commentaire ou un admin seulement
        if ($this->getUser() !== $commentaire->getUtilisateur()
            && ! $this->isGranted('ROLE_ADMIN')
        ) {
            throw $this->createAccessDeniedException('Vous ne pouvez pas supprimer ce commentaire.');
        }

        // 2) Vérification CSRF
        if ($this->isCsrfTokenValid('delete_comment_'.$commentaire->getId(), $request->request->get('_token'))) {
            $em->remove($commentaire);
            $em->flush();
        }

        // 3) Retour à l’article parent
        return $this->redirectToRoute('app_article_show', [
            'id' => $commentaire->getArticle()->getId()
        ]);
    }
}
