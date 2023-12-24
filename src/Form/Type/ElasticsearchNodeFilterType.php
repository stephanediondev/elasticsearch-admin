<?php

declare(strict_types=1);

namespace App\Form\Type;

use App\Manager\CallManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ElasticsearchNodeFilterType extends AbstractType
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

        $fields[] = 'roles';
        if (1 < count($options['version'])) {
            $fields[] = 'version';
        }
        if (true === $this->callManager->hasFeature('cat_sort')) {
            $fields[] = 'sort';
        }
        $fields[] = 'page';

        foreach ($fields as $field) {
            switch ($field) {
                case 'roles':
                    $builder->add($field, ChoiceType::class, [
                        'placeholder' => '-',
                        'choices' => $options['roles'],
                        'choice_label' => static function ($choice, $key, $value) use ($options) {
                            return 'node_roles.'.$options['roles'][$key];
                        },
                        'label' => 'roles',
                        'required' => false,
                        'multiple' => true,
                        'attr' => [
                            'data-break-after' => 'yes',
                        ],
                    ]);
                    break;
                case 'version':
                    $builder->add($field, ChoiceType::class, [
                        'placeholder' => '-',
                        'choices' => $options['version'],
                        'choice_label' => static function ($choice, $key, $value) use ($options) {
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

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'csrf_protection' => false,
            'roles' => ['m', 'd', 'h', 'w', 'c', 's', 'f', 'i', 'v', 'l', 't', 'r'],
            'version' => [],
        ]);
    }

    public function getBlockPrefix(): string
    {
        return '';
    }
}
