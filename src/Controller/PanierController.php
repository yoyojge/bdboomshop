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
        // return $this->render('shop/index.html.twig', [
        //     'products' => $productRepository->findAll(),
        //     'categories' => $categoryRepository->findAll(),
        //     'QtiteItemCart' =>  $QtiteItemCart
        // ]);
        return $this->redirectToRoute('app_shop_index', [], Response::HTTP_SEE_OTHER);
        
    }


    #[Route('/panierShow', name: 'app_shop_panierShow', methods: ['GET', 'POST'])]
    public function panierShow(   Request $request, ProductRepository $productRepository, CategoryRepository $categoryRepository,  Session $session): Response
    {   
        $QtiteItemCart = $session->get('QtiteItemCart', 0); 
        
        //on recupere la session panier
        $panier = $session->get('panier', []); 

        return $this->render('shop/panierShow.html.twig', [
            'products' => $productRepository->findAll(),
            'categories' => $categoryRepository->findAll(),
            'QtiteItemCart' =>  $QtiteItemCart,
            'panier' =>  $panier,

        ]);
    }

    
    #[Route('/panierVide', name: 'app_shop_panierVide', methods: ['GET', 'POST'])]
    public function panierVide(   Request $request, ProductRepository $productRepository, CategoryRepository $categoryRepository,  Session $session): Response
    {   
        $session->set('panier', []);
        $session->set('QtiteItemCart',  0);
        return $this->redirectToRoute('app_shop_panierShow', [], Response::HTTP_SEE_OTHER);
    }




    // PAYPAL :: https://grafikart.fr/tutoriels/paypal-checkout-standard-962
    public function ui(Cart $cart): string
    {
        $clientId = PAYPAL_ID;
        $order = json_encode([
            'purchase_units' => [
                [
                    'description' => 'Panier tutoriel grafikart',
                    'items'       => array_map(function ($product) {
                        return [
                            'name'        => $product['name'],
                            'quantity'    => 1,
                            'unit_amount' => [
                                'value'         => number_format($product['price'] / 100, 2, '.', ""), // Mes sommes sont en centimes d'euros
                                'currency_code' => 'EUR',
                            ]
                        ];
                    }, $cart->getProducts()),
                    'amount'      => [
                        'currency_code' => 'EUR',
                        'value'         => number_format($cart->getTotal() / 100, 2, '.', ""),
                        'breakdown'     => [
                            'item_total' => [
                                'currency_code' => 'EUR',
                                'value'         => number_format($cart->getTotal() / 100, 2, '.', "")
                            ]
                        ]
                    ]
                ]
            ]
        ]);
        return <<<HTML
        <script src="https://www.paypal.com/sdk/js?client-id={$clientId}&currency=EUR&intent=authorize"></script>
        <div id="paypal-button-container"></div>
        <script>
          paypal.Buttons({
            // Sets up the transaction when a payment button is clicked
            createOrder: (data, actions) => {
              return actions.order.create({$order});
            },
            // Finalize the transaction after payer approval
            onApprove: async (data, actions) => {
              const authorization = await actions.order.authorize()
              const authorizationId = authorization.purchase_units[0].payments.authorizations[0].id
              await fetch('/paypal.php', {
                method: 'post',
                headers: {
                  'content-type': 'application/json'
                },
                body: JSON.stringify({authorizationId})
              })
              alert('Votre paiement a bien été enregistré')
            }
          }).render('#paypal-button-container');
        </script>
    HTML;
    }


    public function handle(ServerRequestInterface $request, Cart $cart): void
    {
        if ($this->sandbox) {
            $environment = new \PayPalCheckoutSdk\Core\SandboxEnvironment($this->clientId, $this->clientSecret);
        } else {
            $environment = new \PayPalCheckoutSdk\Core\ProductionEnvironment($this->clientId, $this->clientSecret);
        }
        $client = new \PayPalCheckoutSdk\Core\PayPalHttpClient($environment);
        $authorizationId = $request->getParsedBody()['authorizationId'];
        $request = new \PayPalCheckoutSdk\Payments\AuthorizationsGetRequest($authorizationId);
        $authorizationResponse = $client->execute($request);
        if ($authorizationResponse->result->amount->value !== number_format($cart->getTotal() / 100, 2, '.', "")) {
            throw new PaymentAmountMissmatchException($amount, $cart->getTotal());
        }

        // On peut récupérer l'Order créé par le bouton
        $orderId = $authorizationResponse->result->supplementary_data->related_ids->order_id;
        // $request = new OrdersGetRequest($orderId);
        // $orderResponse = $client->execute($request);

        // Vérifier si le stock est dispo

        // Verrouiller le produit (retirer du stock pour éviter une commande en parallèle entre temps)

        // Sauvegarder les informations de l'utilisateur

        // On capture l'autorisation
        $request = new AuthorizationsCaptureRequest($authorizationId);
        $response = $client->execute($request);
        if ($response->result->status !== 'COMPLETED') {
            throw new \Exception();
        }
    }
    





}
