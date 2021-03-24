<?php
namespace App\Form\Type;

use App\Entity\Category;
use App\Entity\Product;
use App\Repository\CategoryRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class ProductType extends AbstractType
{
    private $categoryRepository;
    public function __construct(CategoryRepository $categoryRepository)
    {
        $this->categoryRepository=$categoryRepository;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $categories=$this->categoryRepository->findAll();

        $builder
        ->add('name', TextType::class)
        ->add('description', TextType::class)
        ->add('quantity',IntegerType::class)
        ->add('price',NumberType::class)
        ->add ('image',FileType::class,[
            'mapped'=>false,
            'label'    => 'inserer une image ',
            'constraints' => [
            new File([
                'maxSize' => '1024k',
                'mimeTypes' => [
                    'image/jpeg',
                    'image/png'
                ],
                'mimeTypesMessage' => 'Please upload a valid image file',
            ])
    ],
        ])
        ->add('category',EntityType::class,[
            'class'=>Category::class,
            'choice_label' => 'name',
        ])
        ->add('TTC', CheckboxType::class, [
            'mapped'=>false,
            'label'    => 'inclue  le TVA?',
            'required' => $options['required_ttc'],
        ])
        ->add('save', SubmitType::class, ['label' => 'Create product'])
       ;
    }
    public function  configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class'=>Product::class,
            'required_ttc'=>true,
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            'csrf_token_id'   => 'product_item',
        ]);
    }
}