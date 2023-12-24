<?php

declare(strict_types=1);

namespace App\Form\Type;

use App\Manager\CallManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ElasticsearchTaskFilterType extends AbstractType
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

        $fields[] = 'node';
        $fields[] = 'page';

        $options['master_node'] = $this->callManager->getMasterNode();

        foreach ($fields as $field) {
            switch ($field) {
                case 'node':
                    $builder->add('node', ChoiceType::class, [
                        'multiple' => true,
                        'choices' => $options['node'],
                        'choice_label' => static function ($choice, $key, $value) use ($options) {
                            if ($options['master_node'] === $options['node'][$key]) {
                                return $options['node'][$key].' [Master]';
                            } else {
                                return $options['node'][$key];
                            }
                        },
                        'choice_translation_domain' => false,
                        'label' => 'node',
                        'required' => false,
                        'attr' => [
                            'size' => 3,
                        ],
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
            'node' => [],
        ]);
    }

    public function getBlockPrefix(): string
    {
        return '';
    }
}
