<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class CreateIndexType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $fields = [];

        $fields[] = 'name';
        $fields[] = 'number_of_shards';
        $fields[] = 'number_of_replicas';

        foreach ($fields as $field) {
            switch ($field) {
                case 'name':
                    $builder->add('name', TextType::class, [
                        'label' => 'name',
                        'required' => true,
                        'constraints' => [
                            new NotBlank(),
                        ],
                    ]);
                    break;
                case 'number_of_shards':
                    $builder->add('number_of_shards', IntegerType::class, [
                        'data' => 5,
                        'label' => 'number_of_shards',
                        'required' => true,
                        'constraints' => [
                            new NotBlank(),
                        ],
                        'attr' => [
                            'min' => 1,
                            'max' => 1024,
                        ],
                    ]);
                    break;
                case 'number_of_replicas':
                    $builder->add('number_of_replicas', IntegerType::class, [
                        'data' => 1,
                        'label' => 'number_of_replicas',
                        'required' => true,
                        'constraints' => [
                            new NotBlank(),
                        ],
                        'attr' => [
                            'min' => 0,
                        ],
                    ]);
                    break;
            }
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([

        ]);
    }

    public function getBlockPrefix()
    {
        return 'data';
    }
}
