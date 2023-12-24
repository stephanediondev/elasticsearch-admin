<?php

declare(strict_types=1);

namespace App\Form\Type;

use App\Manager\CallManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ElasticsearchIndexFilterType extends AbstractType
{
    protected CallManager $callManager;

    public function __construct(CallManager $callManager)
    {
        $this->callManager = $callManager;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->setMethod('GET');

        $fields = [];

        $fields[] = 'name';
        $fields[] = 'status';
        $fields[] = 'health';
        $fields[] = 'system';
        if (true === $this->callManager->hasFeature('cat_sort')) {
            $fields[] = 'sort';
        }
        $fields[] = 'page';

        foreach ($fields as $field) {
            switch ($field) {
                case 'system':
                    $builder->add('system', ChoiceType::class, [
                        'placeholder' => '-',
                        'choices' => $options['question'],
                        'choice_label' => static function ($choice, $key, $value) use ($options) {
                            return $options['question'][$key];
                        },
                        'label' => 'system',
                        'required' => false,
                    ]);
                    break;
                case 'name':
                    $builder->add('name', TextType::class, [
                        'label' => 'name',
                        'required' => false,
                        'attr' => [
                            'data-break-after' => 'yes',
                        ],
                    ]);
                    break;
                case 'status':
                    $builder->add('status', ChoiceType::class, [
                        'placeholder' => '-',
                        'choices' => $options['status'],
                        'choice_label' => static function ($choice, $key, $value) use ($options) {
                            return $options['status'][$key];
                        },
                        'label' => 'status',
                        'required' => false,
                        'attr' => [
                            'data-break-after' => 'yes',
                        ],
                    ]);
                    break;
                case 'health':
                    $builder->add('health', ChoiceType::class, [
                        'multiple' => true,
                        'choices' => $options['health'],
                        'choice_label' => static function ($choice, $key, $value) use ($options) {
                            return $options['health'][$key];
                        },
                        'label' => 'health',
                        'required' => false,
                        'attr' => [
                            'data-break-after' => 'yes',
                            'size' => count($options['health']),
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

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'csrf_protection' => false,
            'status' => ['open', 'close'],
            'health' => ['red', 'yellow', 'green'],
            'question' => ['yes', 'no'],
        ]);
    }

    public function getBlockPrefix(): string
    {
        return '';
    }
}
