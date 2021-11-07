<?php

namespace App\Form\Type;

use App\Manager\CallManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class ElasticsearchNodeFilterType extends AbstractType
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

        $fields[] = 'master';
        $fields[] = 'data';
        if (true === $this->callManager->hasFeature('voting_only')) {
            $fields[] = 'voting_only';
        }
        $fields[] = 'ingest';
        if (1 < count($options['version'])) {
            $fields[] = 'version';
        }
        if (true === $this->callManager->hasFeature('cat_sort')) {
            $fields[] = 'sort';
        }
        $fields[] = 'page';

        foreach ($fields as $field) {
            switch ($field) {
                case 'master':
                case 'data':
                case 'voting_only':
                case 'ingest':
                    $builder->add($field, ChoiceType::class, [
                        'placeholder' => '-',
                        'choices' => $options['question'],
                        'choice_label' => function ($choice, $key, $value) use ($options) {
                            return $options['question'][$key];
                        },
                        'label' => 'node_roles.'.$field,
                        'required' => false,
                        'attr' => [
                            'data-break-after' => 'yes',
                        ],
                    ]);
                    break;
                case 'version':
                    $builder->add($field, ChoiceType::class, [
                        'placeholder' => '-',
                        'choices' => $options['version'],
                        'choice_label' => function ($choice, $key, $value) use ($options) {
                            return $options['version'][$key];
                        },
                        'label' => 'version',
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
            'version' => [],
        ]);
    }

    public function getBlockPrefix(): string
    {
        return '';
    }
}
