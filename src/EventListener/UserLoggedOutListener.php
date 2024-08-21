<?php

namespace App\EventListener;

use App\Entity\HistoriqueConnexion;
use App\Entity\LigneCommande;
use App\Entity\Panier;
use App\Event\UserLoggedInEvent;
use App\Repository\ArticleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class UserLoggedOutListener
{
    public function __construct(private EntityManagerInterface $manager, private ArticleRepository $articleRepository, private RequestStack $requestStack)
    {
        
    }

    public function onUserLoggedOut(UserLoggedInEvent $event)
    {
        $utilisateur = $event->getUtilisateur();
        $session = $this->requestStack->getSession();

        if (!is_null($utilisateur)) {
            $userId = $utilisateur->getId();
            $panierSession = $session->get('panier', []);
            $articlesPanier = [];

            $total = 0;
            $quantiteTotale = 0;

            if (isset($panierSession[$userId])) {
                foreach($panierSession[$userId] as $articleId => $quantite) {
                    $article = $this->articleRepository->find($articleId);
                    $prixTotalArticle = $article->getPrixUnitaire() * $quantite;
                    $total += $prixTotalArticle;
                    $quantiteTotale += $quantite;

                    $articlesPanier[] = [
                        'article' => $article,
                        'quantite' => $quantite,
                        'prix_total' => $prixTotalArticle,
                    ];
                }
            }

            $panier = new Panier();
            $panier->setUtilisateur($utilisateur)->setMontantTotal($total);
            $this->manager->persist($panier);
            
            foreach($articlesPanier as $articlePanier) {
                $ligneCommande = new LigneCommande();
                $ligneCommande->setArticle($articlePanier['article'])
                                ->setPanier($panier)
                                ->setQuantite($articlePanier['quantite'])
                                ->setPrix($articlePanier['prix_total']);
                $this->manager->persist($ligneCommande);
            }
            
            $this->manager->flush();

            $session->remove('panier');
        }
    }
}
