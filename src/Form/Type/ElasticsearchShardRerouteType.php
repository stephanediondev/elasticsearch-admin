<?php

namespace App\Form\Type;

use App\Model\ElasticsearchShardRerouteModel;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class ElasticsearchShardRerouteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $fields = [];

        $fields[] = 'command';
        $fields[] = 'to_node';

        foreach ($fields as $field) {
            switch ($field) {
                case 'command':
                    $builder->add('command', ChoiceType::class, [
                        'placeholder' => '-',
                        'choices' => $options['commands'],
                        'choice_label' => function ($choice, $key, $value) use ($options) {
                            return 'reroute_commands.'.$options['commands'][$key];
                        },
                        'label' => 'command',
                        'required' => true,
                        'constraints' => [
                            new NotBlank(),
                        ],
                    ]);

                    break;
                case 'to_node':
                    $builder->add('to_node', ChoiceType::class, [
                        'placeholder' => '-',
                        'choices' => $options['nodes'],
                        'choice_label' => function ($choice, $key, $value) use ($options) {
                            return $options['nodes'][$key];
                        },
                        'choice_translation_domain' => false,
                        'label' => 'to_node',
                        'required' => false,
                    ]);
                    break;
            }
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ElasticsearchShardRerouteModel::class,
            'commands' => [],
            'nodes' => [],
        ]);
    }

    public function getBlockPrefix()
    {
        return 'data';
    }
}
