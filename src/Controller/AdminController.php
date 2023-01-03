<?php

namespace App\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


use App\Entity\Product;
use App\Form\ProductType;
use App\Repository\ProductRepository;

use Symfony\Component\HttpFoundation\Request;

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

    






















    
}
