<?php

namespace App\Form;

use App\Model\ElasticsearchIndexModel;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Json;

class CreateIndexType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $fields = [];

        $fields[] = 'name';
        $fields[] = 'number_of_shards';
        $fields[] = 'number_of_replicas';
        $fields[] = 'mappings';

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
                        'label' => 'number_of_shards',
                        'required' => true,
                        'constraints' => [
                            new NotBlank(),
                            new GreaterThanOrEqual(1),
                        ],
                        'attr' => [
                            'min' => 1,
                            'max' => 1024,
                        ],
                    ]);
                    break;
                case 'number_of_replicas':
                    $builder->add('number_of_replicas', IntegerType::class, [
                        'label' => 'number_of_replicas',
                        'required' => true,
                        'constraints' => [
                            new NotBlank(),
                            new GreaterThanOrEqual(0),
                        ],
                        'attr' => [
                            'data-break-after' => 'yes',
                            'min' => 0,
                        ],
                    ]);
                    break;
                case 'mappings':
                    $builder->add('mappings', TextareaType::class, [
                        'label' => 'mappings',
                        'required' => false,
                        'constraints' => [
                            new Json(),
                        ],
                    ]);
                    break;
            }
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ElasticsearchIndexModel::class,
        ]);
    }

    public function getBlockPrefix()
    {
        return 'data';
    }
}
