<?php

namespace App\Controller;

use App\Entity\Product;
use App\Form\ProductType;
use App\Repository\ProductRepository;

use App\Entity\Category;
use App\Form\CategoryType;
use App\Repository\CategoryRepository;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;

use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

// use Symfony\Component\HttpFoundation\RequestStack;
// $session = $requestStack->getSession();


use Symfony\Component\HttpFoundation\Session\Session;

class PanierController extends AbstractController
{
    
    
    #[Route('/panier', name: 'app_shop_panier', methods: ['GET', 'POST'])]
    public function panier(   Request $request, ProductRepository $productRepository, CategoryRepository $categoryRepository,  Session $session): Response
    {      
               
    
        // $arrayProductSessionTab[ $request->request->get('product') ] = $request->request->get('qtite');
        $id = $request->request->get('product');

        //je recupere la session si elle existe , sinon, c'est un tableau vide
        $panier = $session->get('panier', []);
        

        if(!empty($panier[$id])){
            $panier[$id] += $request->request->get('qtite');
            
        }
        else{
            $panier[$id] = $request->request->get('qtite');
        }
        
        //je mets a jour la session
        $session->set('panier',  $panier);

        $QtiteItemCart = $session->get('QtiteItemCart', 0);
        $QtiteItemCart += $request->request->get('qtite');
        $session->set('QtiteItemCart',  $QtiteItemCart);

        // dd($session);

        
        $session->getFlashBag()->add(
            'Bravo',
            'Vos articles ont été ajoutés au panier'
        );       
    
        // session_unset();




    
        return $this->render('shop/index.html.twig', [
            'products' => $productRepository->findAll(),
            'categories' => $categoryRepository->findAll(),
            'QtiteItemCart' =>  $QtiteItemCart

        ]);
        
    }


    





}
