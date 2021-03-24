<?php

namespace App\Controller;

use App\Entity\Product;
use App\Form\Type\ProductType;
use App\Repository\ProductRepository;
use App\Services\CalculPrixTTC;
use App\Form\Handlers\ProductFormHandler;
use Doctrine\DBAL\Types\FloatType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class ProductController extends AbstractController
{
    private $session;
    private $productRepository;
    private $calculPrixTTC;
    private $productFormHandler;
    public function __construct(ProductFormHandler $productFormHandler,ProductRepository $productRepository, SessionInterface $session, CalculPrixTTC $calculPrixTTC)
    {
        $this->productRepository=$productRepository;
        $this->session=$session;
        $this->calculPrixTTC=$calculPrixTTC;
        $this->productFormHandler=$productFormHandler;
    }

    /**
     * @Route("/product", name="product")
     */
    public function index(): Response
    {
        $products= $this->getDoctrine()->getRepository(Product::class)->findAll();

        return $this->render('product/index.html.twig', [
            'controller_name' => 'ProductController',
            'products'=>$products
        ]);
    }
    /**
     * @Route("/product/add", name="product.add")
     */
    public function addproduct(Request $request): Response
    {
        $product = new Product();
        $form=$this->createForm(ProductType::class,$product,[
            'required_ttc'=> true
        ]);
        if($this->productFormHandler->handle($form,$request))
        {
            return $this->redirectToRoute('product');
        }else
        {
            $errors = $form->getErrors(true);
            return $this->render('product/addProduct.html.twig', [
                'form' => $form->createView(),
                'errors'=>$errors
            ]);
        }
    }
    /**
     * @Route("/deleteproduct/{id}", name="deleteproduct")
     */
    public function delete(Request $request,$id) {

        $submittedToken = $request->request->get('token');
        if ($this->isCsrfTokenValid('delete-product', $submittedToken))
        {
            //find the object to delete
            $product = $this->getDoctrine()->getRepository(Product::class)->find($id);
            // delete object
            $this->productRepository->deleteproduct($product);
            // add alert success
            $this->session->getFlashBag()->add(
                'success',
                'your abject has been deleted with success !'
            );
            //redirect to index
            return $this->redirectToRoute('product');
        }else
        {
            $this->session->getFlashBag()->add(
                'danger',
                ' the token is invalid !!!!!!! !'
            );
            //redirect to index
            return $this->redirectToRoute('product');
        }
    }
    /**
     * @Route("/showproduct/{id}")
     */
    public function show($id) {
        //find the object to show
        $product = $this->getDoctrine()->getRepository(Product::class)->find($id);

        //redirect to index
        return $this->redirectToRoute('product');
    }
    /**
     * @Route("/editproduct/{id}")
     */
    public function edit(Request $request , Product $product) {

        $form=$this->createForm(ProductType::class,$product,[
            'required_ttc'=> true
        ]);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid() )
        {
            $product=$form->getData();
            $checkTTC=$form->get('TTC')->getData();
            if($checkTTC==true)
            {
                $product->setPrice($product->getPrice()+$product->getPrice()*0.20);
            }
            $this->productRepository->editProduct();
            //redirect to index
            return $this->redirectToRoute('product');
        }
        $errors = $form->getErrors(true);
        return $this->render('product/editProduct.html.twig', [
            'form' => $form->createView(),
            'errors'=>$errors
        ]);
    }
}
