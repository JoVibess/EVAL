<?php
namespace App\DataFixtures;

use App\Entity\User;
use App\Entity\Article;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        // Création des utilisateurs
        $admin = new User();
        $admin->setEmail('admin@example.com');
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setPassword($this->passwordHasher->hashPassword(
            $admin,
            'adminpass'
        ));
        $manager->persist($admin);

        $author1 = new User();
        $author1->setEmail('author1@example.com');
        $author1->setRoles(['ROLE_USER']);
        $author1->setPassword($this->passwordHasher->hashPassword(
            $author1,
            'authorpass1'
        ));
        $manager->persist($author1);

        $author2 = new User();
        $author2->setEmail('author2@example.com');
        $author2->setRoles(['ROLE_USER']);
        $author2->setPassword($this->passwordHasher->hashPassword(
            $author2,
            'authorpass2'
        ));
        $manager->persist($author2);

        // Titres des articles
        $titles = [
            'Aventure en montagne',
            'Découverte de la mer',
            'Road trip à travers l’Europe',
            'Séjour nature',
            'Exploration urbaine',
            'Randonnée dans les Alpes',
            'Voyage historique',
            'Détente au bord du lac',
            'Safari en Afrique',
            'Plongée sous-marine'
        ];

        foreach ($titles as $i => $title) {
            $article = new Article();
            $article->setTitre($title);
            // Contenu avec environ 8 phrases
            $content = sprintf(
                "Cet article présente un aperçu de %s. Il décrit l'histoire et la culture locales. Les paysages environnants offrent une vue spectaculaire. Les habitants sont accueillants et chaleureux. Vous pourrez découvrir des sites historiques fascinants. La gastronomie locale ravira vos papilles. Des activités en plein air sont disponibles toute l'année. En somme, ce voyage promet des souvenirs inoubliables.",
                $title
            );
            $article->setContenu($content);
            $article->setCreatedAt(new \DateTimeImmutable(sprintf('2025-04-%02d', $i + 1)));
            $article->setUpdatedAt(
                (clone (new \DateTimeImmutable(sprintf('2025-04-%02d', $i + 1))))->modify('+1 day')
            );
            // Attribution des auteurs en alternance
            $author = ($i % 2 === 0) ? $author1 : $author2;
            $article->setAuteur($author);
            $manager->persist($article);
        }

        $manager->flush();
    }
}
