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
            //'shards/{index}',
            'master',
            'nodes',
            'tasks',
            'indices',
            //'indices/{index}',
            'segments',
            //'segments/{index}',
            'count',
            //'count/{index}',
            'recovery',
            //'recovery/{index}',
            'health',
            'pending_tasks',
            'aliases',
            //'aliases/{alias}',
            'thread_pool',
            //'thread_pool/{thread_pools}',
            'plugins',
            'fielddata',
            //'fielddata/{fields}',
            'nodeattrs',
            'repositories',
            //'snapshots/{repository}',
            'templates',
        ];
        sort($commands);

        $fields = [];

        $fields[] = 'command';
        $fields[] = 'headers';
        $fields[] = 'sort';

        foreach ($fields as $field) {
            switch ($field) {
                case 'command':
                    $builder->add('command', ChoiceType::class, [
                        'placeholder' => '-',
                        'choices' => $commands,
                        'choice_label' => function ($choice, $key, $value) use ($commands) {
                            return $commands[$key];
                        },
                        'label' => 'command',
                        'required' => true,
                        'constraints' => [
                            new NotBlank(),
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
        ]);
    }

    public function getBlockPrefix()
    {
        return 'data';
    }
}
