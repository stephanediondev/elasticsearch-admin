<?php

namespace App\Form;

use App\Model\ElasticsearchCatModel;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class FilterCatType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $commands = [
            'allocation',
            'shards',
            'shards/{index}',
            'master',
            'nodes',
            'tasks',
            'indices',
            'indices/{index}',
            'segments',
            'segments/{index}',
            'count',
            'count/{index}',
            'recovery',
            'recovery/{index}',
            'health',
            'pending_tasks',
            'aliases',
            'aliases/{alias}',
            'thread_pool',
            //'thread_pool/{thread_pools}',
            'plugins',
            'fielddata',
            //'fielddata/{fields}',
            'nodeattrs',
            'repositories',
            'snapshots/{repository}',
            'templates',
        ];
        sort($commands);

        $fields = [];

        $fields[] = 'command';
        $fields[] = 'index';
        $fields[] = 'repository';
        $fields[] = 'alias';
        $fields[] = 'headers';
        $fields[] = 'sort';

        $builder->setMethod('GET');

        foreach ($fields as $field) {
            switch ($field) {
                case 'command':
                    $builder->add('command', ChoiceType::class, [
                        'placeholder' => '-',
                        'choices' => $commands,
                        'choice_label' => function ($choice, $key, $value) use ($commands) {
                            return $commands[$key];
                        },
                        'choice_translation_domain' => false,
                        'label' => 'command',
                        'required' => true,
                        'constraints' => [
                            new NotBlank(),
                        ],
                    ]);
                    break;
                case 'index':
                    $builder->add('index', ChoiceType::class, [
                        'placeholder' => '-',
                        'choices' => $options['indices'],
                        'choice_label' => function ($choice, $key, $value) use ($options) {
                            return $options['indices'][$key];
                        },
                        'choice_translation_domain' => false,
                        'label' => 'index',
                        'required' => false,
                    ]);
                    break;
                case 'repository':
                    $builder->add('repository', ChoiceType::class, [
                        'placeholder' => '-',
                        'choices' => $options['repositories'],
                        'choice_label' => function ($choice, $key, $value) use ($options) {
                            return $options['repositories'][$key];
                        },
                        'choice_translation_domain' => false,
                        'label' => 'repository',
                        'required' => false,
                    ]);
                    break;
                case 'alias':
                    $builder->add('alias', ChoiceType::class, [
                        'placeholder' => '-',
                        'choices' => $options['aliases'],
                        'choice_label' => function ($choice, $key, $value) use ($options) {
                            return $options['aliases'][$key];
                        },
                        'choice_translation_domain' => false,
                        'label' => 'alias',
                        'required' => false,
                        'attr' => [
                            'data-break-after' => 'yes',
                        ],
                    ]);
                    break;
                case 'headers':
                    $builder->add('headers', TextType::class, [
                        'label' => 'headers',
                        'required' => false,
                    ]);
                    break;
                case 'sort':
                    $builder->add('sort', TextType::class, [
                        'label' => 'sort',
                        'required' => false,
                    ]);
                    break;
            }
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ElasticsearchCatModel::class,
            'repositories' => [],
            'indices' => [],
            'aliases' => [],
        ]);
    }

    public function getBlockPrefix()
    {
        return 'data';
    }
}
