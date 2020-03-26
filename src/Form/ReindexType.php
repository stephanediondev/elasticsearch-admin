<?php

namespace App\Form;

use App\Model\ElasticsearchReindexModel;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class ReindexType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $fields = [];

        $fields[] = 'source';
        $fields[] = 'dest';

        foreach ($fields as $field) {
            switch ($field) {
                case 'source':
                    $builder->add('source', ChoiceType::class, [
                        'placeholder' => '-',
                        'choices' => $options['indices'],
                        'choice_label' => function ($choice, $key, $value) use ($options) {
                            return $options['indices'][$key];
                        },
                        'label' => 'source',
                        'required' => true,
                        'constraints' => [
                            new NotBlank(),
                        ],
                    ]);
                    break;
                case 'dest':
                    $builder->add('dest', ChoiceType::class, [
                        'placeholder' => '-',
                        'choices' => $options['indices'],
                        'choice_label' => function ($choice, $key, $value) use ($options) {
                            return $options['indices'][$key];
                        },
                        'label' => 'dest',
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
            'data_class' => ElasticsearchReindexModel::class,
            'indices' => [],
        ]);
    }

    public function getBlockPrefix()
    {
        return 'data';
    }
}
