<?php

namespace App\Form\Type;

use App\Model\ElasticsearchReindexModel;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class ReindexType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $fields = [];

        $fields[] = 'source';
        $fields[] = 'destination';

        foreach ($fields as $field) {
            switch ($field) {
                case 'source':
                    $builder->add('source', TextType::class, [
                        'label' => 'source',
                        'required' => true,
                        'constraints' => [
                            new NotBlank(),
                        ],
                        'help' => 'help_form.reindex.source',
                    ]);
                    break;
                case 'destination':
                    $builder->add('destination', ChoiceType::class, [
                        'placeholder' => '-',
                        'choices' => $options['indices'],
                        'choice_label' => function ($choice, $key, $value) use ($options) {
                            return $options['indices'][$key];
                        },
                        'choice_translation_domain' => false,
                        'label' => 'destination',
                        'required' => true,
                        'constraints' => [
                            new NotBlank(),
                        ],
                        'help' => 'help_form.reindex.dest',
                    ]);
                    break;
            }
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ElasticsearchReindexModel::class,
            'indices' => [],
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'data';
    }
}
