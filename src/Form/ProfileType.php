<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProfileType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'label' => 'Adresse email',
            ])
            ->add('firstname', TextType::class, [
                'required' => false,
                'label' => 'Prénom',
            ])
            ->add('lastname', TextType::class, [
                'required' => false,
                'label' => 'Nom',
            ])
            ->add('phone', TelType::class, [
                'required' => false,
                'label' => 'Téléphone',
            ])
            ->add('address', TextType::class, [
                'required' => false,
                'label' => 'Adresse postale',
            ])
            ->add('postalCode', TextType::class, [
                'required' => false,
                'label' => 'Code postal',
            ])
            ->add('city', TextType::class, [
                'required' => false,
                'label' => 'Ville',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
