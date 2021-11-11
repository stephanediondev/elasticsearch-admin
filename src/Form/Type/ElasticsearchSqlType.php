<?php

namespace App\Form\Type;

use App\Model\ElasticsearchSqlModel;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Json;

class ElasticsearchSqlType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $fields = [];

        $fields[] = 'query';
        $fields[] = 'filter';
        $fields[] = 'fetch_size';

        foreach ($fields as $field) {
            switch ($field) {
                case 'query':
                    $builder->add('query', TextareaType::class, [
                        'label' => 'query',
                        'required' => true,
                        'constraints' => [
                            new NotBlank(),
                        ],
                        'attr' => [
                            'data-break-after' => 'yes',
                        ],
                    ]);
                    break;
                case 'filter':
                    $builder->add('filter', TextareaType::class, [
                        'label' => 'filter',
                        'required' => false,
                        'constraints' => [
                            new Json(),
                        ],
                        'attr' => [
                            'data-break-after' => 'yes',
                        ],
                    ]);
                    break;
                case 'fetch_size':
                    $builder->add('fetch_size', IntegerType::class, [
                        'label' => 'fetch_size',
                        'required' => true,
                        'constraints' => [
                            new NotBlank(),
                        ],
                    ]);
                    break;
            }
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ElasticsearchSqlModel::class,
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'data';
    }
}
