<?php

namespace App\Form\Type;

use App\Manager\CallManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class ElasticsearchDataStreamFilterType extends AbstractType
{
    protected $callManager;

    public function __construct(CallManager $callManager)
    {
        $this->callManager = $callManager;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->setMethod('GET');

        $fields = [];

        $fields[] = 'name';
        if (true === $this->callManager->hasFeature('data_stream_expand_wildcards')) {
            $fields[] = 'hidden';
        }
        $fields[] = 'status';
        if (true === $this->callManager->hasFeature('cat_sort')) {
            $fields[] = 'sort';
        }
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
                case 'hidden':
                    $builder->add('hidden', ChoiceType::class, [
                        'placeholder' => '-',
                        'choices' => $options['question'],
                        'choice_label' => function ($choice, $key, $value) use ($options) {
                            return $options['question'][$key];
                        },
                        'label' => 'hidden',
                        'required' => false,
                        'attr' => [
                            'data-break-after' => 'yes',
                        ],
                    ]);
                    break;
                case 'status':
                    $builder->add('status', ChoiceType::class, [
                        'multiple' => true,
                        'choices' => $options['status'],
                        'choice_label' => function ($choice, $key, $value) use ($options) {
                            return $options['status'][$key];
                        },
                        'label' => 'status',
                        'required' => false,
                        'attr' => [
                            'size' => count($options['status']),
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
            'status' => ['red', 'yellow', 'green'],
        ]);
    }

    public function getBlockPrefix()
    {
        return '';
    }
}
