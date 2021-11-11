<?php

namespace App\Form\Type;

use App\Model\ElasticsearchSnapshotRestoreModel;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ElasticsearchSnapshotRestoreType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $fields = [];

        $fields[] = 'rename_pattern';
        $fields[] = 'rename_replacement';
        $fields[] = 'ignore_unavailable';
        $fields[] = 'partial';
        $fields[] = 'include_global_state';
        $fields[] = 'indices';

        foreach ($fields as $field) {
            switch ($field) {
                case 'rename_pattern':
                    $builder->add('rename_pattern', TextType::class, [
                        'label' => 'rename_pattern',
                        'required' => false,
                    ]);
                    break;
                case 'rename_replacement':
                    $builder->add('rename_replacement', TextType::class, [
                        'label' => 'rename_replacement',
                        'required' => false,
                    ]);
                    break;
                case 'indices':
                    $builder->add('indices', ChoiceType::class, [
                        'multiple' => true,
                        'choices' => $options['indices'],
                        'choice_label' => function ($choice, $key, $value) use ($options) {
                            return $options['indices'][$key];
                        },
                        'choice_translation_domain' => false,
                        'label' => 'indices',
                        'required' => false,
                        'attr' => [
                        ],
                    ]);
                    break;
                case 'ignore_unavailable':
                    $builder->add('ignore_unavailable', CheckboxType::class, [
                        'label' => 'ignore_unavailable',
                        'required' => false,
                    ]);
                    break;
                case 'partial':
                    $builder->add('partial', CheckboxType::class, [
                        'label' => 'partial',
                        'required' => false,
                    ]);
                    break;
                case 'include_global_state':
                    $builder->add('include_global_state', CheckboxType::class, [
                        'label' => 'include_global_state',
                        'required' => false,
                        'attr' => [
                            'data-break-after' => 'yes',
                        ],
                    ]);
                    break;
            }
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ElasticsearchSnapshotRestoreModel::class,
            'indices' => [],
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'data';
    }
}
