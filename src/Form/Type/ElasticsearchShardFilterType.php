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

class ElasticsearchShardFilterType extends AbstractType
{
    public function __construct(CallManager $callManager)
    {
        $this->callManager = $callManager;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->setMethod('GET');

        $fields = [];

        $fields[] = 'index';
        $fields[] = 'state';
        $fields[] = 'node';
        if (true === $this->callManager->hasFeature('cat_sort')) {
            $fields[] = 'sort';
        }
        $fields[] = 'page';

        foreach ($fields as $field) {
            switch ($field) {
                case 'index':
                    $builder->add('index', TextType::class, [
                        'label' => 'index',
                        'required' => false,
                        'attr' => [
                            'data-break-after' => 'yes',
                        ],
                    ]);
                    break;
                case 'state':
                    $builder->add('state', ChoiceType::class, [
                        'multiple' => true,
                        'choices' => $options['state'],
                        'choice_label' => function ($choice, $key, $value) use ($options) {
                            return $options['state'][$key];
                        },
                        'label' => 'state',
                        'required' => false,
                        'attr' => [
                            'data-break-after' => 'yes',
                        ],
                    ]);
                    break;
                case 'node':
                    $builder->add('node', ChoiceType::class, [
                        'multiple' => true,
                        'choices' => $options['node'],
                        'choice_label' => function ($choice, $key, $value) use ($options) {
                            return $options['node'][$key];
                        },
                        'choice_translation_domain' => false,
                        'label' => 'node',
                        'required' => false,
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
            'state' => ['initializing', 'relocating', 'started', 'unassigned'],
            'node' => [],
        ]);
    }

    public function getBlockPrefix()
    {
        return '';
    }
}
