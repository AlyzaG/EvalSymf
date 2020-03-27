<?php

namespace App\Controller;

use App\Entity\Produit;
use App\Entity\Panier;
use App\Form\FicheProduitType;
use App\Form\PanierType;
use App\Form\ProduitsType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AppController extends AbstractController
{
    /**
     * @Route("/home", name="home")
     */
    public function index(Request $request,  EntityManagerInterface $entityManager)
    {
        try {

            $PanierRepository = $this->getDoctrine()
                ->getRepository(Panier::class)
                ->findAll();

            $ProduitRepository = $this->getDoctrine()
                ->getRepository(Produit::class)
                ->findAll();
        } catch (\Exception $e){
            return new Response('Erreur bdd');
        }


            $totalQte = 0;
            $totalMontant = 0;
            $prix = 0;
            foreach ($PanierRepository as $panier ) {
                    $totalQte += $panier->getQuantite();
                    $totalMontant += $panier->getProduit()->getPrix();

                $prix = $totalQte+$totalMontant;
                $prixTotal = $prix+$totalQte;
            }

        return $this->render('app/index.html.twig', [
            'panier'=> $PanierRepository,
            'Quantite' => $totalQte,
            'somme' =>$prix,
            'total' => $prixTotal

        ]);
    }


    /**
     * @Route("/produit", name="produit")
     */
    public function produit(Request $request,  EntityManagerInterface $entityManager)
    {
        $produit= new Produit();

        $produitRepository = $this->getDoctrine()
            ->getRepository(Produit::class)
            ->findAll();


        $form = $this->createForm(ProduitsType::class, $produit);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $produit= $form->getData();

            $image = $produit->getImage();
            $imageName = md5(uniqid()).'.'.$image->guessExtension();
            $imageName->move($this->getParameters('upload_files') ,
                $imageName);
            $produit ->setImage($imageName);


            $entityManager->persist($produit);
            $entityManager->flush();

            return $this->redirectToRoute('produit');
        }

        return $this->render('app/produits.html.twig', [
            'produit' => $produitRepository,
            'formProduit' => $form->createView()
        ]);
    }

    /**
     * @Route("/ficheProduit/{id}", name="ficheProduit")
     */
    public function ficheProduit($id, Request $request,  EntityManagerInterface $entityManager)
    {
        $panier =new Panier();

        $ProduitRepository = $this->getDoctrine()
            ->getRepository(Produit::class)
            ->find($id);


        $form = $this->createForm(FicheProduitType::class, $panier);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){

            $panier= $form->getData();
            $panier->setEtat(false);
            $panier->setProduit($ProduitRepository);

            $entityManager->persist($panier);
            $entityManager->flush();

        }

        return $this->render('app/ficheProduit.html.twig', [
            'formPanier'=> $form->createView(),
            'produit'=> $ProduitRepository
        ]);
    }

    /**
 * @Route("/ficheProduit/remove/{id}", name="remove")
 */
    public function remove($id, EntityManagerInterface $entityManager){
        $produit = $this->getDoctrine()->getRepository(Produit::class)->find($id);

        $entityManager->remove($produit);
        $entityManager->flush();

        return $this->redirectToRoute('produit');
    }

    /**
     * @Route("/remove/{id}", name="remove")
     */
    public function removePanier($id, EntityManagerInterface $entityManager){
        $panier = $this->getDoctrine()->getRepository(Produit::class)->find($id);

        $entityManager->remove($panier);
        $entityManager->flush();

        return $this->redirectToRoute('home');
    }
}
