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

use Symfony\Component\HttpFoundation\Session\Session;



class ShopController extends AbstractController
{
    
    // public function __construct( protected int $QtiteItemCart) {        
    //     // $this->QtiteItemCart = $session->get('QtiteItemCart', 0);
    //     if(!empty($_SESSION['QtiteItemCart'])){
    //         $this->QtiteItemCart = $_SESSION['QtiteItemCart'];
    //     }
    //     else{
    //         $this->QtiteItemCart = 0;
    //     }
       
    // }
    
    
    
    #[Route('/', name: 'app_shop_index', methods: ['GET'])]
    public function shopIndex(ProductRepository $productRepository, CategoryRepository $categoryRepository,  Session $session  ): Response
    {          
        $QtiteItemCart = $session->get('QtiteItemCart', 0);      
        
        return $this->render('shop/index.html.twig', [
            'products' => $productRepository->findAll(),
            'categories' => $categoryRepository->findAll(),
            'QtiteItemCart' =>  $QtiteItemCart,
        ]);
    }


    //PAGE LISTE PRODUITS
    #[Route('/books', name: 'app_shop_product', methods: ['GET'])]
    public function shopProduts(ProductRepository $productRepository, CategoryRepository $categoryRepository, Request $request,  Session $session): Response
    {
        
        // dd( $request->query->get('idCat') );
        $QtiteItemCart = $session->get('QtiteItemCart', 0);  
        
        if(!empty($request->query->get('idCat'))){
            return $this->render('product/index.html.twig', [
                'products' => $productRepository->findBy( array('category_id' => $request->query->get('idCat') ) ),
                'categories' => $categoryRepository->findAll(),
                'QtiteItemCart' =>  $QtiteItemCart,
            ]);

        }
        else{
            return $this->render('product/index.html.twig', [
                'products' => $productRepository->findAll(),
                'categories' => $categoryRepository->findAll(),
                'QtiteItemCart' =>  $QtiteItemCart,
            ]);
        }
        
    }


    //PAGE DETAIL
    #[Route('/book/{id}', name: 'app_shop_product_show', methods: ['GET'])]
    public function show(Product $product, ProductRepository $productRepository, CategoryRepository $categoryRepository, Request $request,  Session $session): Response
    {
        
        // dd($product);
        $QtiteItemCart = $session->get('QtiteItemCart', 0);  
        
        return $this->render('product/show.html.twig', [
            'product' => $product,
            'categories' => $categoryRepository->findAll(),
            'QtiteItemCart' =>  $QtiteItemCart,
        ]);
    }



    #[Route('/inscription', name: 'app_shop_user_new', methods: ['GET', 'POST'])]
    public function shopNewUser(Request $request, UserRepository $userRepository,  UserPasswordHasherInterface $passwordHasher, CategoryRepository $categoryRepository,  Session $session): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        $QtiteItemCart = $session->get('QtiteItemCart', 0);  

        if ($form->isSubmitted() && $form->isValid()) {

            $password = $passwordHasher->hashPassword($user, $request->get('user')['password']);
            $user->setPassword ($password);
            $user->setToken( $user->createToken() );
            $user->setRoles(['ROLE_USER']);

            $userRepository->save($user, true);

            return $this->redirectToRoute('app_shop_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('user/new.html.twig', [
            'user' => $user,
            'form' => $form,
            'categories' => $categoryRepository->findAll(),
            'QtiteItemCart' =>  $QtiteItemCart,
        ]);
    }





}
