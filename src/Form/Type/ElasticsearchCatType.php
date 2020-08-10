<?php

namespace App\Form\Type;

use App\Manager\CallManager;
use App\Model\ElasticsearchCatModel;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class ElasticsearchCatType extends AbstractType
{
    public function __construct(CallManager $callManager)
    {
        $this->callManager = $callManager;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $commands = [
            'aliases',
            'aliases/{alias}',
            'allocation',
            'allocation/{node}',
            'count',
            'count/{index}',
            'fielddata',
            //'fielddata/{field}',
            'health',
            'indices',
            'indices/{index}',
            'master',
            'nodes',
            'pending_tasks',
            'plugins',
            'recovery',
            'recovery/{index}',
            'shards',
            'shards/{index}',
            'segments',
            'segments/{index}',
            'thread_pool',
        ];
        if (true === $this->callManager->hasFeature('cat_transforms')) {
            $commands[] = 'transforms';
        }
        if (true === $this->callManager->hasFeature('cat_ml')) {
            $commands[] = 'ml/anomaly_detectors';
            $commands[] = 'ml/datafeeds';
            $commands[] = 'ml/data_frame/analytics';
            $commands[] = 'ml/trained_models';
        }
        if (true === $this->callManager->hasFeature('cat_tasks')) {
            $commands[] = 'tasks';
        }
        if (true === $this->callManager->hasFeature('cat_templates')) {
            $commands[] = 'templates';
        }
        if (true === $this->callManager->hasFeature('cat_repositories_snapshots')) {
            $commands[] = 'repositories';
            $commands[] = 'snapshots/{repository}';
        }
        if (true === $this->callManager->hasFeature('cat_nodeattrs')) {
            $commands[] = 'nodeattrs';
        }
        sort($commands);

        $fields = [];

        $fields[] = 'command';
        $fields[] = 'index';
        $fields[] = 'repository';
        $fields[] = 'alias';
        $fields[] = 'node';
        $fields[] = 'headers';
        if (true === $this->callManager->hasFeature('cat_sort')) {
            $fields[] = 'sort';
        }

        $builder->setMethod('GET');

        foreach ($fields as $field) {
            switch ($field) {
                case 'command':
                    $builder->add('command', ChoiceType::class, [
                        'placeholder' => '-',
                        'choices' => $commands,
                        'choice_label' => function ($choice, $key, $value) use ($commands) {
                            return $commands[$key];
                        },
                        'choice_translation_domain' => false,
                        'label' => 'command',
                        'required' => true,
                        'constraints' => [
                            new NotBlank(),
                        ],
                    ]);
                    break;
                case 'index':
                    $builder->add('index', ChoiceType::class, [
                        'placeholder' => '-',
                        'choices' => $options['indices'],
                        'choice_label' => function ($choice, $key, $value) use ($options) {
                            return $options['indices'][$key];
                        },
                        'choice_translation_domain' => false,
                        'label' => 'index',
                        'required' => false,
                    ]);
                    break;
                case 'repository':
                    $builder->add('repository', ChoiceType::class, [
                        'placeholder' => '-',
                        'choices' => $options['repositories'],
                        'choice_label' => function ($choice, $key, $value) use ($options) {
                            return $options['repositories'][$key];
                        },
                        'choice_translation_domain' => false,
                        'label' => 'repository',
                        'required' => false,
                    ]);
                    break;
                case 'alias':
                    $builder->add('alias', ChoiceType::class, [
                        'placeholder' => '-',
                        'choices' => $options['aliases'],
                        'choice_label' => function ($choice, $key, $value) use ($options) {
                            return $options['aliases'][$key];
                        },
                        'choice_translation_domain' => false,
                        'label' => 'alias',
                        'required' => false,
                    ]);
                    break;
                case 'node':
                    $builder->add('node', ChoiceType::class, [
                        'placeholder' => '-',
                        'choices' => $options['nodes'],
                        'choice_label' => function ($choice, $key, $value) use ($options) {
                            return $options['nodes'][$key];
                        },
                        'choice_translation_domain' => false,
                        'label' => 'node',
                        'required' => false,
                        'attr' => [
                            'data-break-after' => 'yes',
                        ],
                    ]);
                    break;
                case 'headers':
                    $builder->add('headers', TextType::class, [
                        'label' => 'headers',
                        'required' => false,
                    ]);
                    break;
                case 'sort':
                    $builder->add('sort', TextType::class, [
                        'label' => 'sort',
                        'required' => false,
                    ]);
                    break;
            }
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ElasticsearchCatModel::class,
            'repositories' => [],
            'indices' => [],
            'aliases' => [],
            'nodes' => [],
        ]);
    }

    public function getBlockPrefix()
    {
        return 'data';
    }
}
