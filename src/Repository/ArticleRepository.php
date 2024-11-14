<?php

// Déclaration de l'espace de noms pour le dépôt Article
namespace App\Repository;

// Importation des classes nécessaires
use App\Entity\Article;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Article>
 *
 * La classe ArticleRepository étend ServiceEntityRepository pour gérer les opérations liées à l'entité Article.
 * 
 * @method Article|null find($id, $lockMode = null, $lockVersion = null)
 * @method Article|null findOneBy(array $criteria, array $orderBy = null)
 * @method Article[]    findAll()
 * @method Article[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ArticleRepository extends ServiceEntityRepository
{
    // Constructeur de la classe qui initialise le dépôt avec le registre de gestion
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Article::class);
    }

    /**
     * Cette méthode récupère les nouveaux articles disponibles (non supprimés) et les trie par ID par ordre décroissant.
     * 
     * @param int $maxResults Nombre maximum de résultats à retourner (par défaut : 15)
     * @return array Liste des articles correspondant
     */
    public function recupererNouveautes(int $maxResults = 25)
    {
        return $this->createQueryBuilder('a') // 'a' est un alias pour Article
                    ->Where('a.supprime = 0') // Filtre pour ne sélectionner que les articles non supprimés
                    ->orderBy('a.id', 'DESC') // Trie par ID décroissant (du plus récent au plus ancien)
                    ->setMaxResults($maxResults) // Limite les résultats à $maxResults
                    ->getQuery() // Génère la requête finale
                    ->getResult(); // Exécute la requête et retourn les résultats
    }

    /**
     * Cette méthode récupère les articles qui sont les meilleures vente, c'est-à-dire ceux qui ont atteint ou dépassé un seuil de ventes total.
     * 
     * @param int $total_ventes Le seuil minimum de ventes pour qu'un article soit considéré comme une "meilleure vente"
     * @return array Liste des articles correspondant
     */
    public function recupererMeilleuresVentes(int $total_ventes = 5)
    {
        return $this->createQueryBuilder('a') // Construction de la requête
                    ->select('a, c, SUM(lc.quantite) AS total_ventes') // Sélection de l'article, sa catégorie et le total des ventes
                    ->leftJoin('a.categorie', 'c') // Jointure avec l'entité Categorie
                    ->leftJoin('a.ligneCommandes', 'lc') // Jointure avec les lignes de commande
                    ->leftJoin('lc.panier', 'p') // Jointure avec le panier pour accéder aux achats
                    ->leftJoin('p.historiqueAchats', 'ha') // Jointure avec l'historique des achats
                    ->Where('a.supprime = 0') // Filtre pour exclure les articles supprimés
                    ->groupBy('a.id') // Groupement par article
                    ->having('total_ventes >= :total_ventes') // Filtre pour ne sélectionner que les articles ayant un total de ventes supérieur ou égal à $total_vzntes
                    ->setParameter('total_ventes', $total_ventes) // Paramètre du total des ventes
                    ->orderBy('total_ventes', 'DESC') // Tri des articles selon les ventes, du plus vendu au moins vendu
                    ->getQuery() // Génère la requête finale
                    ->getResult(); // Exécute la requête et retourne les résultats
    }

    /**
     * Cette méthode permet de filtrer les articles selon une catégorie et/ou un mot-clé dans le titre.
     * 
     * @param string $categorie Catégorie de l'article
     * @param string $motCle Mot-clé à rechercher dans le titre de l'article
     * @return array Liste des articles correspondant
     */
    public function filtrerArticlesPar(string $categorie, string $motCle) {
        // Création de la requête de base
        $queryBuilder = $this->createQueryBuilder('a');

        // Ajoute une condition pour filtrer par catégorie si elle est définie
        if (!empty($categorie)) {
            $queryBuilder->andWhere('a.categorie = :categorie')
                            ->setParameter('categorie', $categorie);
        }

        // Ajoute une condition pour filtrer par mot-clé dans le titre s'il est fourni
        if (!empty($motCle)) {
            $queryBuilder->andWhere('a.titre LIKE :motCle')
                            ->setParameter('motCle', '%' . $motCle . '%');
        }

        // Exécute la requête et retourne les résultats
        return $queryBuilder->getQuery()->getResult();
    }
}