<?php
namespace App\Services;

use App\Entity\Product;
use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class ProductFormHandler
{
    private $session;
    private $productRepository;
    private $calculPrixTTC;
    private $parameterBag;
    public function __construct(ParameterBagInterface $parameterBag,ProductRepository $productRepository, SessionInterface $session, CalculPrixTTC $calculPrixTTC)
    {
        $this->productRepository=$productRepository;
        $this->session=$session;
        $this->calculPrixTTC=$calculPrixTTC;
        $this->parameterBag=$parameterBag;
    }
    public function handle($form,Request $request)
    {

        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid() )
        {
            $product=$form->getData();
            $product->setCreatedAt(new \DateTime());

            // insert image
            $image=$form->get('image')->getData();
            $fileName = (md5(uniqid())).'.'.$image->guessExtension();
            $image->move(
                $this->parameterBag->get('ImagesUpload'),
                $fileName
            );
            $product->setImage($fileName);
            $checkTTC=$form->get('TTC')->getData();
            if($checkTTC==true)
            {
                $product->setPrice($this->calculPrixTTC->calculerPrixTTC($product->getPrice()));
            }
            $this->productRepository->addproduct($product);
            $this->session->getFlashBag()->add(
                'success',
                'your abject has been added with success !'
            );
            return true;
        }
        return false;
    }
}