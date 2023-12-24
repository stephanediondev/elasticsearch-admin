<?php

declare(strict_types=1);

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ElasticsearchUserFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->setMethod('GET');

        $fields = [];

        if ('user' == $options['context']) {
            $fields[] = 'enabled';
        }
        $fields[] = 'reserved';
        $fields[] = 'deprecated';
        $fields[] = 'sort';
        $fields[] = 'page';

        foreach ($fields as $field) {
            switch ($field) {
                case 'enabled':
                case 'reserved':
                case 'deprecated':
                    $builder->add($field, ChoiceType::class, [
                        'placeholder' => '-',
                        'choices' => $options['question'],
                        'choice_label' => static function ($choice, $key, $value) use ($options) {
                            return $options['question'][$key];
                        },
                        'label' => $field,
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

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'csrf_protection' => false,
            'question' => ['yes', 'no'],
            'context' => null,
        ]);
    }

    public function getBlockPrefix(): string
    {
        return '';
    }
}
