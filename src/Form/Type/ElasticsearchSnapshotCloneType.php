<?php

declare(strict_types=1);

namespace App\Form\Type;

use App\Model\ElasticsearchSnapshotCloneModel;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class ElasticsearchSnapshotCloneType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $fields = [];

        $fields[] = 'target_name';
        $fields[] = 'indices';

        foreach ($fields as $field) {
            switch ($field) {
                case 'target_name':
                    $builder->add('target_name', TextType::class, [
                        'label' => 'target_name',
                        'required' => true,
                        'constraints' => [
                            new NotBlank(),
                        ],
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
            'data_class' => ElasticsearchSnapshotCloneModel::class,
            'indices' => [],
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'data';
    }
}
