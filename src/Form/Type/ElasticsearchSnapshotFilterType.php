<?php

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class ElasticsearchSnapshotFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->setMethod('GET');

        $fields = [];

        $fields[] = 'name';
        $fields[] = 'repository';
        $fields[] = 'state';
        $fields[] = 'sort';
        $fields[] = 'page';

        foreach ($fields as $field) {
            switch ($field) {
                case 'name':
                    $builder->add('name', TextType::class, [
                        'label' => 'name',
                        'required' => false,
                        'attr' => [
                            'data-break-after' => 'yes',
                        ],
                    ]);
                    break;
                case 'repository':
                    $builder->add('repository', ChoiceType::class, [
                        'multiple' => true,
                        'choices' => $options['repository'],
                        'choice_label' => function ($choice, $key, $value) use ($options) {
                            return $options['repository'][$key];
                        },
                        'choice_translation_domain' => false,
                        'label' => 'repository',
                        'required' => false,
                        'attr' => [
                            'size' => count($options['state']),
                            'data-break-after' => 'yes',
                        ],
                    ]);
                    break;
                case 'state':
                    $builder->add('state', ChoiceType::class, [
                        'multiple' => true,
                        'choices' => $options['state'],
                        'choice_label' => function ($choice, $key, $value) use ($options) {
                            return $options['state'][$key];
                        },
                        'label' => 'state',
                        'required' => false,
                        'attr' => [
                            'size' => count($options['state']),
                        ],
                    ]);
                    break;
                case 'sort':
                    $builder->add('sort', HiddenType::class, [
                        'label' => 'sort',
                        'required' => false,
                    ]);
                    break;
                case 'page':
                    $builder->add('page', HiddenType::class, [
                        'label' => 'page',
                        'required' => false,
                    ]);
                    break;
            }
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'csrf_protection' => false,
            'state' => ['failed', 'incompatible', 'in_progress', 'partial', 'success'],
            'repository' => [],
        ]);
    }

    public function getBlockPrefix()
    {
        return '';
    }
}
