<?php

namespace App\Form\Type;

use App\Manager\CallManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ElasticsearchTemplateFilterType extends AbstractType
{
    public function __construct(CallManager $callManager)
    {
        $this->callManager = $callManager;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->setMethod('GET');

        $fields = [];

        $fields[] = 'name';
        if ('index_template' == $options['context'] && true === $this->callManager->hasFeature('data_streams')) {
            $fields[] = 'data_stream';
        }
        if ('index_template_legacy' != $options['context']) {
            $fields[] = 'managed';
        }
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
                case 'data_stream':
                    $builder->add('data_stream', ChoiceType::class, [
                        'placeholder' => '-',
                        'choices' => $options['question'],
                        'choice_label' => function ($choice, $key, $value) use ($options) {
                            return $options['question'][$key];
                        },
                        'label' => 'data_stream',
                        'required' => false,
                        'attr' => [
                            'data-break-after' => 'yes',
                        ],
                    ]);
                    break;
                case 'managed':
                    $builder->add('managed', ChoiceType::class, [
                        'placeholder' => '-',
                        'choices' => $options['question'],
                        'choice_label' => function ($choice, $key, $value) use ($options) {
                            return $options['question'][$key];
                        },
                        'label' => 'managed',
                        'required' => false,
                        'attr' => [
                            'data-break-after' => 'yes',
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
            'question' => ['yes', 'no'],
            'context' => null,
        ]);
    }

    public function getBlockPrefix()
    {
        return '';
    }
}
