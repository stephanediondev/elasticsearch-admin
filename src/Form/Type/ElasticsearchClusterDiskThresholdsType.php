<?php

namespace App\Form\Type;

use App\Model\ElasticsearchClusterDiskThresholdsModel;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class ElasticsearchClusterDiskThresholdsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $fields = [];

        $fields[] = 'enabled';
        $fields[] = 'low';
        $fields[] = 'high';
        $fields[] = 'flood_stage';

        foreach ($fields as $field) {
            switch ($field) {
                case 'enabled':
                    $builder->add('enabled', CheckboxType::class, [
                        'label' => 'enabled',
                        'required' => false,
                    ]);
                    break;
                case 'low':
                    $builder->add('low', TextType::class, [
                        'label' => 'watermark_low',
                        'required' => true,
                        'constraints' => [
                            new NotBlank(),
                        ],
                    ]);
                    break;
                case 'high':
                    $builder->add('high', TextType::class, [
                        'label' => 'watermark_high',
                        'required' => true,
                        'constraints' => [
                            new NotBlank(),
                        ],
                    ]);
                    break;
                case 'flood_stage':
                    $builder->add('flood_stage', TextType::class, [
                        'label' => 'watermark_flood_stage',
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
        $resolver->setDefaults([
            'data_class' => ElasticsearchClusterDiskThresholdsModel::class,
        ]);
    }

    public function getBlockPrefix()
    {
        return 'data';
    }
}
