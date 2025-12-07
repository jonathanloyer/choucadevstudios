<?php

namespace App\Form;

use App\Entity\BillingDocument;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotBlank;

class BillingDocumentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // Client concerné par le devis / la facture
            ->add('client', EntityType::class, [
                'class' => User::class,
                'choice_label' => function (User $user) {
                    $fullName = $user->getFullName();
                    return $fullName !== '' ? $fullName . ' (' . $user->getEmail() . ')' : $user->getEmail();
                },
                'label' => 'Client',
                'placeholder' => 'Sélectionner un client',
            ])

            // Type de document
            ->add('type', ChoiceType::class, [
                'label' => 'Type de document',
                'choices' => [
                    'Devis' => 'quote',
                    'Facture' => 'invoice',
                    'Documents' => 'others',
                ],
            ])

            // Fichier PDF
            ->add('file', FileType::class, [
                'label' => 'Fichier PDF',
                'mapped' => false, // pas lié directement à l’entité
                'required' => true,
                'constraints' => [
                    new NotBlank(['message' => 'Merci de sélectionner un fichier PDF.']),
                    new File([
                        'maxSize' => '10M',
                        'mimeTypes' => ['application/pdf', 'application/x-pdf'],
                        'mimeTypesMessage' => 'Merci d\'uploader un fichier PDF valide.',
                    ]),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => BillingDocument::class,
        ]);
    }
}
