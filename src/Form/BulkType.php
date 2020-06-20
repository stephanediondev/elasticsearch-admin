<?php

namespace App\Form;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormError;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class BulkType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $fields = [];

        $fields[] = 'bulk_file';

        foreach ($fields as $field) {
            switch ($field) {
                case 'bulk_file':
                    $builder->add('bulk_file', FileType::class, [
                        'label' => 'bulk_file',
                        'required' => true,
                        'constraints' => [
                            new NotBlank(),
                        ],

                    ]);
                    break;
            }
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
    }

    public function getBlockPrefix()
    {
        return 'data';
    }
}
