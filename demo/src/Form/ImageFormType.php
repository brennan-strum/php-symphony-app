<?php

namespace App\Form;

use App\Entity\Image;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ImageFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('url', FileType::class, [
                'required' => false,
                'mapped' => false,
                'label' => 'Photo',
                'attr' => array(
                    'class' => 'form-input file-input w-full max-w-xs'
                )
            ])
            ->add('description', TextareaType::class, [
                'attr' => array(
                    'class' => 'form-input textarea textarea-bordered textarea-lg w-full max-w-xs form-description'
                )
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Image::class,
        ]);
    }
}
