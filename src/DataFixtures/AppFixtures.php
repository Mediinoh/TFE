<?php

    namespace App\DataFixtures;

    // Importation des classes nécessaires
    use App\Entity\Article;
    use App\Entity\Categorie;
    use App\Entity\Utilisateur;
    use Doctrine\Bundle\FixturesBundle\Fixture;
    use Doctrine\Persistence\ObjectManager;
    use Faker\Factory;
    use Faker\Generator;

    // La classe AppFixtures étend Fixture pour fournir des données de test.
    class AppFixtures extends Fixture
    {
        /**
         * @var Generator Instance de Faker pour générer des données fictives en français
         */
        private Generator $faker;

        // Constructeur qui initialiser Faker avec la localisation 'fr-FR'
        public function __construct()
        {
            $this->faker = Factory::create('fr-FR');
        }

        // Méthode principale appelée par Doctrice pour charger les fixtures dans la base de données
        public function load(ObjectManager $manager) : void
        {
            // Création d'un compte administrateur avec des rôles 'ROLE_USER' et 'ROLE_ADMIN'
            $admin = new Utilisateur();
            $admin->setPrenom('Admin')->setNom('Admin')->setAdresse('Admin')->setCodePostal('Admin')
                ->setEmail('admin@admin.admin')->setDateNaissance(new \DateTimeImmutable('2005-02-20'))
                ->setPseudo('admin')->setPlainPassword('admin')->setRoles(['ROLE_USER', 'ROLE_ADMIN']);
            
            // Persistance de l'administrateur
            $manager->persist($admin);

            // Détermine le nombre d'utilisateurs à créer, par défaut 0, modifiable par une variable d'environnement 'NB_USERS'
            $nbUsers = intval(getenv('NB_USERS') ?: 0);

            // Boucle pour générer des utilisateurs fictifs
            for ($i = 1; $i <= $nbUsers; $i++) {
                // Génère une date de naissance entre 18 et 60 ans dans le passé
                $dateNaissance = $this->faker->dateTimeBetween('-60 years', '-18 years');
                
                // Instancie un nouvel utilisateur
                $user = new Utilisateur();
                $user->setPrenom($this->faker->firstName())
                     ->setNom($this->faker->lastName())
                     ->setAdresse($this->faker->address)
                    ->setCodePostal($this->faker->postcode())
                    ->setEmail($this->faker->email())
                    ->setDateNaissance(\DateTimeImmutable::createFromMutable($dateNaissance))
                    ->setPseudo($this->faker->userName())
                    ->setPlainPassword('user')
                    ->setRoles(['ROLE_USER']);
                
                // Persistance de l'utilisateur
                $manager->persist($user);
            }

            // Création d'une liste de noms de catégories prédéfinis et tableau pour stocker les objets catégorie
            $categories_list = ['informatique', 'livre', 'hi-fi', 'Ordinateur', 'Téléphone', 'Accessoire', 'Jeux', 'Console'];
            $categories = [];
            
            // Boucle pour créer et persister chaque catégorie en utilisant la liste prédéfinie
            foreach($categories_list as $nom_categorie) {
                $categorie = new Categorie();
                $categorie->setNomCategorie($nom_categorie);
                $manager->persist($categorie);
                $categories[] = $categorie;
            }
            
            // Liste d'articles avec leurs caractéristiques (titre, prix, description, catégorie, et image)
            $articles_list = [
                ['titre' => 'Illusions perdues', 'prix' => 12.75, 'description' => 'Livre intitulé "Illusions perdues" de Honoré de Balzac', 'categorie_id' => 2, 'photo_article' => 'illusions_perdues.jpg'],
                ['titre' => 'Ordinateur portable HP', 'prix' => 599, 'description' => 'Ordinateur Portable HP Laptop 15S-FQ5045NB 15.6" Intel Core i5 8 Go DDR4 512 Go SSD', 'categorie_id' => 4, 'photo_article' => 'HP-LAPTOP-15S-FQ5045NB-15-6-512-8-I5-1235U-INTEGRATED.jpg'],
                ['titre' => 'MICROSYSTÈME HI-FI', 'prix' => 250, 'description' => 'MICROSYSTÈME HI-FI\r\nXL-B517D(BK)', 'categorie_id' => 3, 'photo_article' => 'microsysteme-hi-fi.jpeg'],
                ['titre' => 'Casque Sans fil Steelplay', 'prix' => 29.99, 'description' => 'Steelplay Impulse Camo Bluetooth pour PS4/PS5', 'categorie_id' => 6, 'photo_article' => 'Casque-sans-fil-Steelplay-Impulse-Camo-Bluetooth-pour-PS5-PS4-Xbox-Series-XS-Xbox-One-Nintendo-Switch-PC-ordinateur-portable-et-Mac-Noir-et-Blanc.jpg'],
                ['titre' => 'Casque filaire Xbox', 'prix' => 19.99, 'description' => 'Casque gaming filaire Xbox', 'categorie_id' => 6, 'photo_article' => 'Casque-Gaming-Filaire-Xbox.jpg'],
                ['titre' => 'Nintendo Switch', 'prix' => 299.99, 'description' => 'Console de jeu vidéo Nintendo Switch', 'categorie_id' => 8, 'photo_article' => 'Nintendo_Switch.jpg'],
                ['titre' => 'HP Laptop Zbook', 'prix' => 289.99, 'description' => 'HP Zbook 16 GB de ram SSD 512 GB', 'categorie_id' => 4, 'photo_article' => 'HP-LAPTOP-15S-FQ5045NB-15-6-512-8-I5-1235U-INTEGRATED.jpg'],
                ['titre' => 'Jeux PC Minecraft', 'prix' => 28.78, 'description' => 'Jeu PC Minecraft licence.', 'categorie_id' => 7, 'photo_article' => 'minecraft.jpg'],
                ['titre' => 'Pack pc gaming', 'prix' => 580, 'description' => 'Pack pc gaming complet avec clavier et souris', 'categorie_id' => 1, 'photo_article' => 'tour_pc_24pouces.jpg'],
                ['titre' => 'jeux vidéo XBOX 360', 'prix' => 35.89, 'description' => 'Jeux XBOX', 'categorie_id' => 7, 'photo_article' => 'ufc2009.jpg'],
                ['titre' => 'Fifa 23 Xbox One', 'prix' => 38.99, 'description' => 'Jeux pour Xbox One Fifa 23', 'categorie_id' => 7, 'photo_article' => 'fifa23.jpg'],
                ['titre' => 'Station d\'accueil USB', 'prix' => 68.99, 'description' => 'Docking Station HP', 'categorie_id' => 6, 'photo_article' => 'docking_HP.jpg'],
                ['titre' => 'Manette Xbox', 'prix' => 69.98, 'description' => 'Manette  Xbox', 'categorie_id' => 6, 'photo_article' => 'Manette-sans-fil-Microsoft-Xbox-Noir.jpg'],
                ['titre' => 'Xbox Manette lite', 'prix' => 48.75, 'description' => 'Manette XBOX Lite exclu Med-Shop', 'categorie_id' => 6, 'photo_article' => 'Manette-Xbox-sans-fil-Elite-Series-2-Noir.jpg'],
                ['titre' => 'Chargeur USB', 'prix' => 15.99, 'description' => 'Chargeur USB-c', 'categorie_id' => 6, 'photo_article' => 'chargeur_micro-USB.jpg'],
                ['titre' => 'Chargeur iPhone', 'prix' => 19.99, 'description' => 'Chargeur iPhone original', 'categorie_id' => 6, 'photo_article' => 'chargeur.jpg'],
                ['titre' => 'Docking Station Dell', 'prix' => 89.65, 'description' => 'Docking station de marque DELL', 'categorie_id' => 6, 'photo_article' => 'docking.jpg'],
                ['titre' => 'Jeux PS4', 'prix' => 39.99, 'description' => 'FC24 pour PS4', 'categorie_id' => 7, 'photo_article' => 'FC24.jpg'],
                ['titre' => 'Tomb Raider', 'prix' => 49.90, 'description' => 'Jeux PS4 tomb raider', 'categorie_id' => 7, 'photo_article' => 'tomb_raider.jpg'],
                ['titre' => 'Écran gaming AOC', 'prix' => 139, 'description' => 'Écran 27\' AOC', 'categorie_id' => 4, 'photo_article' => 'ecran_AOC_gaming.jpg'],
                ['titre' => 'Écran gaming TUF', 'prix' => 119, 'description' => 'Écran 27\' TUF', 'categorie_id' => 4, 'photo_article' => 'ecran_TUF_gaming.jpg'],
                ['titre' => 'Écran Gaming MSI', 'prix' => 149, 'description' => 'Écran gaming MSI curvé 27\' (240 fps)', 'categorie_id' => 4, 'photo_article' => 'ecran_msi_curved.jpg'],
                ['titre' => 'Livre "Pourquoi j\'ai mangé mon père"', 'prix' => 9.99, 'description' => 'Livre de Roy Lewis', 'categorie_id' => 2, 'photo_article' => 'pourquoi_j_ai_mange_mon_pere.jpg'],
                ['titre' => 'Livre "Survivre avec mon père"', 'prix' => 8.99, 'description' => 'Livre de Eva Ruth', 'categorie_id' => 2, 'photo_article' => 'survivre_avec_mon_pere.jpg'],
                ['titre' => 'Livre " Pablo Escobar mon père"', 'prix' => 12.99, 'description' => 'Livre de Juan Pablo Escobar', 'categorie_id' => 2, 'photo_article' => 'pablo_escobar.jpg'],
                ['titre' => 'Écran HP 32"', 'prix' => 285, 'description' => 'Écran curvé de 32 pouces HP', 'categorie_id' => 4, 'photo_article' => 'ecran_HP_32_pouces.jpg'],
                ['titre' => 'Jeux PS4 "CyberPunk"', 'prix' => 26.98, 'description' => 'Jeux CyberPunk PS4', 'categorie_id' => 7, 'photo_article' => 'cyberpunk.jpg'],
            ];
            
            // Boucle de création et persistance des articles
            foreach($articles_list as $article_item) {
                $article = new Article();
                $categorie = $categories[$article_item['categorie_id'] - 1];
                $article->setTitre($article_item['titre'])->setPrixUnitaire($article_item['prix'])
                        ->setDescription($article_item['description'])->setPhotoArticle($article_item['photo_article'])->setCategorie($categorie);
                
                // Ajoute l'article à la catégorie et persiste l'article
                $categorie->addArticle($article);
                $manager->persist($article);
            }

            // Sauvegarde toutes les entités créées en base de données
            $manager->flush();
        }
    }