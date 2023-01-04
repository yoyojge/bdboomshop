<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

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


//pour l'upload
use Symfony\Component\String\Slugger\SluggerInterface;




#[Route('/admin')]
class AdminController extends AbstractController
{
    
    //page par default de l'admin
    #[Route('/dashboard', name: 'app_admin_index', methods: ['GET'])]
    public function index(): Response
    {       
        return $this->renderForm('admin/index.html.twig', []);
    }



    
    /* *******************************/
     /* DEBUT PRODUITS */

    //page d'affichage des produits dans le dashboard de l'admin
    #[Route('/dashboardProduct', name: 'app_admin_product', methods: ['GET'])]
    public function dashboardProduct(ProductRepository $productRepository): Response
    {       
        return $this->renderForm('admin/product.html.twig', ['products' => $productRepository->findAll()]);
    }


    //page d'affichage d ajout de produits dans le dashboard de l'admin
    #[Route('/dashboardProductNew', name: 'app_admin_product_new', methods: ['GET', 'POST'])]
    public function dashboardProductNew(Request $request, ProductRepository $productRepository, SluggerInterface $slugger): Response
    {
        $product = new Product();
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $Now = new \DateTime('now', new \DateTimeZone('Europe/Paris'));
            $product->setDateAjout($Now);

           //gestion de l'upload de fichier
            $imageFile = $form->get('image')->getData();
            $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
            // this is needed to safely include the file name as part of the URL
            $safeFilename = $slugger->slug($originalFilename);
            $newFilename = $safeFilename.'-'.uniqid().'.'.$imageFile->guessExtension();

            // Move the file to the directory where brochures are stored
            try {
                $imageFile->move(
                    "../public/uploads/",
                    $newFilename
                );
            } catch (FileException $e) {
                // ... handle exception if something happens during file upload
            }

            // updates the 'brochureFilename' property to store the PDF file name
            // instead of its contents
            $product->setImage($newFilename);


            $productRepository->save($product, true);

            return $this->renderForm('admin/product.html.twig', ['products' => $productRepository->findAll()]);
        }

        return $this->renderForm('admin/product.new.html.twig', [
            'product' => $product,
            'form' => $form,
        ]);
    
    }

    //affichage du detail d'un produit dans le dashboard admin
    #[Route('/dashboardProduct/{id}', name: 'app_admin_product_show', methods: ['GET'])]
    public function dashboardProductShow(Product $product): Response
    {
        return $this->render('admin/product.show.html.twig', [
            'product' => $product,
        ]);
    }


    //suppression d'un produit depuis l'insteface admin
    #[Route('/dashboardProduct/{id}', name: 'app_admin_product_delete', methods: ['POST'])]
    public function dashboardProductDelete(Request $request, Product $product, ProductRepository $productRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$product->getId(), $request->request->get('_token'))) {
            $productRepository->remove($product, true);
        }

        return $this->renderForm('admin/product.html.twig', ['products' => $productRepository->findAll()]);
    }



    //edition d'un produit dans l'interface d'admin
    #[Route('/dashboardProduct/{id}/edit', name: 'app_admin_product_edit', methods: ['GET', 'POST'])]
    public function dashboardProductEdit(Request $request, Product $product, ProductRepository $productRepository, SluggerInterface $slugger): Response
    {
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {


            //gestion de l'upload de fichier
            $imageFile = $form->get('image')->getData();
            $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
            // this is needed to safely include the file name as part of the URL
            $safeFilename = $slugger->slug($originalFilename);
            $newFilename = $safeFilename.'-'.uniqid().'.'.$imageFile->guessExtension();

            // Move the file to the directory where brochures are stored
            try {
                $imageFile->move(
                    "../public/uploads/",
                    $newFilename
                );
            } catch (FileException $e) {
                // ... handle exception if something happens during file upload
            }

            // updates the 'brochureFilename' property to store the PDF file name
            // instead of its contents
            $product->setImage($newFilename);




            $productRepository->save($product, true);

            return $this->renderForm('admin/product.html.twig', ['products' => $productRepository->findAll()]);
        }

        return $this->renderForm('admin/product.edit.html.twig', [
            'product' => $product,
            'form' => $form,
        ]);
    }

    
    /* FIN PRODUITS */
    /* *******************************/





    /* *******************************/
    /* DEBUT CATEGORY */

    #[Route('/dashboardCategory', name: 'app_admin_category_index', methods: ['GET'])]
    public function dashboardCategory(CategoryRepository $categoryRepository): Response
    {
        return $this->render('category/index.html.twig', [
            'categories' => $categoryRepository->findAll(),
        ]);
    }


    #[Route('/dashboardCategoryNew', name: 'app_admin_category_new', methods: ['GET', 'POST'])]
    public function dashboardCategoryNew(Request $request, CategoryRepository $categoryRepository): Response
    {
        $category = new Category();
        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $categoryRepository->save($category, true);

            return $this->redirectToRoute('app_category_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('category/new.html.twig', [
            'category' => $category,
            'form' => $form,
        ]);
    }



    /* FIN CATEGORY */
    /* *******************************/






    /* *******************************/
    /* DEBUT USERS */

    #[Route('/dashboardUser', name: 'app_admin_user_index', methods: ['GET'])]
    public function dashboardUser(UserRepository $userRepository): Response
    {
             
        return $this->render('user/index.html.twig', [
            'users' => $userRepository->findAll(),
        ]);   

    }



    #[Route('/dashboardUserNew', name: 'app_admin_user_new', methods: ['GET', 'POST'])]
    public function dashboardUserNew(Request $request, UserRepository $userRepository,  UserPasswordHasherInterface $passwordHasher): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $password = $passwordHasher->hashPassword($user, $request->get('user')['password']);
            $user->setPassword ($password);
            $user->setToken( $user->createToken() );
            $user->setRoles(['ROLE_USER']);

            $userRepository->save($user, true);

            return $this->redirectToRoute('app_admin_user_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('admin/user.new.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }


    /* FIN USERS */
    /* *******************************/
















    
}
