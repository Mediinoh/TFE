<?php

    namespace App\DataFixtures;

use App\Entity\Article;
use App\Entity\Categorie;
use App\Entity\Utilisateur;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Faker\Generator;

    class AppFixtures extends Fixture
    {
        /**
         * @var Generator
         */
        private Generator $faker;

        public function __construct()
        {
            $this->faker = Factory::create('fr-FR');
        }

        public function load(ObjectManager $manager) : void
        {
            $admin = new Utilisateur();
            $admin->setPrenom('Admin')->setNom('Admin')->setAdresse('Admin')->setCodePostal('Admin')
                ->setEmail('admin@admin.admin')->setDateNaissance(new \DateTimeImmutable('2005-02-20'))
                ->setPseudo('admin')->setPlainPassword('admin')->setRoles(['ROLE_USER', 'ROLE_ADMIN']);
            $manager->persist($admin);

            $categories_list = ['informatique', 'livre', 'hi-fi', 'Ordinateur', 'Téléphone', 'Accessoire', 'Jeux', 'Console'];
            $categories = [];
            
            foreach($categories_list as $nom_categorie) {
                $categorie = new Categorie();
                $categorie->setNomCategorie($nom_categorie);
                $manager->persist($categorie);
                $categories[] = $categorie;
            }
            
            $articles_list = [
                ['titre' => 'Illusions perdues', 'prix' => 12.75, 'description' => 'Livre intitulée "Illusions perdues" de Honoré de Balzac', 'categorie_id' => 2, 'photo_article' => '41pqcmRAH1L._SL500_.jpg'],
                ['titre' => 'Ordinateur portable HP', 'prix' => 599, 'description' => 'Ordinateur Portable HP Laptop 15S-FQ5045NB 15.6" Intel Core i5 8 Go DDR4 512 Go SSD', 'categorie_id' => 4, 'photo_article' => 'HP-LAPTOP-15S-FQ5045NB-15-6-512-8-I5-1235U-INTEGRATED.jpg'],
                ['titre' => 'MICROSYSTÈME HI-FI', 'prix' => 250, 'description' => 'MICROSYSTÈME HI-FI\r\nXL-B517D(BK)', 'categorie_id' => 3, 'photo_article' => 'microsysteme-hi-fi.jpeg'],
                ['titre' => 'Casque Sans fil Steelplay', 'prix' => 29.99, 'description' => 'Steelplay Impulse Camo Bluetooth pour PS4/PS5', 'categorie_id' => 6, 'photo_article' => 'Casque-sans-fil-Steelplay-Impulse-Camo-Bluetooth-pour-PS5-PS4-Xbox-Series-XS-Xbox-One-Nintendo-Switch-PC-ordinateur-portable-et-Mac-Noir-et-Blanc.jpg'],
                ['titre' => 'Casque filaire Xbox', 'prix' => 19.99, 'description' => 'Casque gaming filaire Xbox', 'categorie_id' => 6, 'photo_article' => 'Casque-Gaming-Filaire-Xbox.jpg'],
                ['titre' => 'Nintendo Switch', 'prix' => 299.99, 'description' => 'Console de jeu vidéo Nintendo Switch', 'categorie_id' => 8, 'photo_article' => 'téléchargement.jpg'],
                ['titre' => 'HP Laptop Zbook', 'prix' => 289.99, 'description' => 'HP Zbook 16 GB de ram SSD 512 GB', 'categorie_id' => 4, 'photo_article' => 'HP-LAPTOP-15S-FQ5045NB-15-6-512-8-I5-1235U-INTEGRATED.jpg'],
                ['titre' => 'Jeux PC Minecraft', 'prix' => 28.78, 'description' => 'Jeu PC Minecraft licence.', 'categorie_id' => 7, 'photo_article' => 'images (1).jpg'],
                ['titre' => 'Pack pc gaming', 'prix' => 580, 'description' => 'Pack pc gaming complet avec clavier et souris', 'categorie_id' => 1, 'photo_article' => 'images (2).jpg'],
                ['titre' => 'jeux vidéo XBOX 360', 'prix' => 35.89, 'description' => 'Jeux XBOX', 'categorie_id' => 7, 'photo_article' => 'images (4).jpg'],
                ['titre' => 'Fifa 23 Xbox One', 'prix' => 38.99, 'description' => 'Jeux pour Xbox One Fifa 23', 'categorie_id' => 7, 'photo_article' => 'images (3).jpg'],
                ['titre' => 'Station d\'accueil USB', 'prix' => 68.99, 'description' => 'Docking Station HP', 'categorie_id' => 6, 'photo_article' => 'images.jpg'],
                ['titre' => 'Manette Xbox', 'prix' => 69.98, 'description' => 'Manette  Xbox', 'categorie_id' => 6, 'photo_article' => 'Manette-sans-fil-Microsoft-Xbox-Noir.jpg'],
                ['titre' => 'Xbox Manette lite', 'prix' => 48.75, 'description' => 'Manette XBOX Lite exclu Med-Shop', 'categorie_id' => 6, 'photo_article' => 'Manette-Xbox-sans-fil-Elite-Series-2-Noir.jpg'],
                ['titre' => 'Chargeur USB', 'prix' => 15.99, 'description' => 'Chargeur USB-c', 'categorie_id' => 6, 'photo_article' => 'téléchargement (1).jpg'],
                ['titre' => 'Chargeur iPhone', 'prix' => 19.99, 'description' => 'Chargeur iPhone original', 'categorie_id' => 6, 'photo_article' => 'téléchargement (2).jpg'],
                ['titre' => 'Docking Station Dell', 'prix' => 89.65, 'description' => 'Docking station de marque DELL', 'categorie_id' => 6, 'photo_article' => 'téléchargement (3).jpg'],
                ['titre' => 'Jeux PS4', 'prix' => 39.99, 'description' => 'FC24 pour PS4', 'categorie_id' => 7, 'photo_article' => 'téléchargement (13).jpg'],
                ['titre' => 'Tomb Raider', 'prix' => 49.90, 'description' => 'Jeux PS4 tomb raider', 'categorie_id' => 7, 'photo_article' => 'télécharment (14).jpg'],
                ['titre' => 'Écran gaming AOC', 'prix' => 139, 'description' => 'Écran 27\' AOC', 'categorie_id' => 4, 'photo_article' => 'téléchargement (17).jpg'],
                ['titre' => 'Écran gaming TUF', 'prix' => 119, 'description' => 'Écran 27\' TUF', 'categorie_id' => 4, 'photo_article' => 'téléchargement (18).jpg'],
                ['titre' => 'Écran Gaming MSI', 'prix' => 149, 'description' => 'Écran gaming MSI curvé 27\' (240 fps)', 'categorie_id' => 4, 'photo_article' => 'téléchargement (19).jpg'],
                ['titre' => 'Livre "Pourquoi j\'ai mangé mon père"', 'prix' => 9.99, 'description' => 'Livre de Roy Lewis', 'categorie_id' => 2, 'photo_article' => 'téléchargement (22).jpg'],
                ['titre' => 'Livre "Survivre avec mon père"', 'prix' => 8.99, 'description' => 'Livre de Eva Ruth', 'categorie_id' => 2, 'photo_article' => 'téléchargement (21).jpg'],
                ['titre' => 'Livre " Pablo Escobar mon père"', 'prix' => 12.99, 'description' => 'Livre de Juan Pablo Escobar', 'categorie_id' => 2, 'photo_article' => 'images (5).jpg'],
                ['titre' => 'Écran HP 32"', 'prix' => 285, 'description' => 'Écran curvé de 32 pouces HP', 'categorie_id' => 4, 'photo_article' => 'téléchargement (20).jpg'],
                ['titre' => 'Jeux PS4 "CyberPunk"', 'prix' => 26.98, 'description' => 'Jeux CyberPunk PS4', 'categorie_id' => 7, 'photo_article' => 'téléchargement (15).jpg'],
            ];
            
            foreach($articles_list as $article_item) {
                $article = new Article();
                $categorie = $categories[$article_item['categorie_id'] - 1];
                $article->setTitre($article_item['titre'])->setPrixUnitaire($article_item['prix'])
                        ->setDescription($article_item['description'])->setPhotoArticle($article_item['photo_article'])->setCategorie($categorie);
                $categorie->addArticle($article);
                $manager->persist($article);
            }
            $manager->flush();
        }
    }