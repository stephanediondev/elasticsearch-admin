<?php

namespace App\Form\Type;

use App\Model\ElasticsearchApplyIlmPolicyModel;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormError;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class ApplyIlmPolicyType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $fields = [];

        $fields[] = 'index_template';
        $fields[] = 'rollover_alias';

        foreach ($fields as $field) {
            switch ($field) {
                case 'index_template':
                    $builder->add('index_template', ChoiceType::class, [
                        'label' => 'index_template_legacy',
                        'placeholder' => '-',
                        'choices' => $options['index_templates'],
                        'choice_label' => function ($choice, $key, $value) use ($options) {
                            return $options['index_templates'][$key];
                        },
                        'choice_translation_domain' => false,
                        'required' => true,
                        'constraints' => [
                            new NotBlank(),
                        ],
                    ]);
                    break;
                case 'rollover_alias':
                    $builder->add('rollover_alias', TextType::class, [
                        'label' => 'rollover_alias',
                        'required' => false,
                    ]);
                    break;
            }
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ElasticsearchApplyIlmPolicyModel::class,
            'index_templates' => [],
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'data';
    }
}
