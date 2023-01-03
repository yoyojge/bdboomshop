<?php

namespace App\Controller;

use App\Entity\PurchasedProduct;
use App\Form\PurchasedProductType;
use App\Repository\PurchasedProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/purchased/product')]
class PurchasedProductController extends AbstractController
{
    #[Route('/', name: 'app_purchased_product_index', methods: ['GET'])]
    public function index(PurchasedProductRepository $purchasedProductRepository): Response
    {
        return $this->render('purchased_product/index.html.twig', [
            'purchased_products' => $purchasedProductRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_purchased_product_new', methods: ['GET', 'POST'])]
    public function new(Request $request, PurchasedProductRepository $purchasedProductRepository): Response
    {
        $purchasedProduct = new PurchasedProduct();
        $form = $this->createForm(PurchasedProductType::class, $purchasedProduct);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $purchasedProductRepository->save($purchasedProduct, true);

            return $this->redirectToRoute('app_purchased_product_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('purchased_product/new.html.twig', [
            'purchased_product' => $purchasedProduct,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_purchased_product_show', methods: ['GET'])]
    public function show(PurchasedProduct $purchasedProduct): Response
    {
        return $this->render('purchased_product/show.html.twig', [
            'purchased_product' => $purchasedProduct,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_purchased_product_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, PurchasedProduct $purchasedProduct, PurchasedProductRepository $purchasedProductRepository): Response
    {
        $form = $this->createForm(PurchasedProductType::class, $purchasedProduct);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $purchasedProductRepository->save($purchasedProduct, true);

            return $this->redirectToRoute('app_purchased_product_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('purchased_product/edit.html.twig', [
            'purchased_product' => $purchasedProduct,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_purchased_product_delete', methods: ['POST'])]
    public function delete(Request $request, PurchasedProduct $purchasedProduct, PurchasedProductRepository $purchasedProductRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$purchasedProduct->getId(), $request->request->get('_token'))) {
            $purchasedProductRepository->remove($purchasedProduct, true);
        }

        return $this->redirectToRoute('app_purchased_product_index', [], Response::HTTP_SEE_OTHER);
    }
}
