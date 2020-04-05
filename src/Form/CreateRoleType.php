<?php

namespace App\Form;

use App\Model\ElasticsearchRoleModel;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Json;

class CreateRoleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $fields = [];

        if (false == $options['update']) {
            $fields[] = 'name';
        }
        $fields[] = 'cluster';
        $fields[] = 'indices';
        $fields[] = 'applications';
        $fields[] = 'run_as';
        $fields[] = 'metadata';

        foreach ($fields as $field) {
            switch ($field) {
                case 'name':
                    $builder->add('name', TextType::class, [
                        'label' => 'name',
                        'required' => true,
                        'constraints' => [
                            new NotBlank(),
                        ],
                    ]);
                    break;
                case 'applications':
                    $builder->add('applications', TextareaType::class, [
                        'label' => 'applications',
                        'required' => false,
                        'constraints' => [
                            new Json(),
                        ],
                    ]);
                    break;
                case 'cluster':
                    $builder->add('cluster', ChoiceType::class, [
                        'multiple' => true,
                        'choices' => $options['cluster'],
                        'choice_label' => function ($choice, $key, $value) use ($options) {
                            return $options['cluster'][$key];
                        },
                        'choice_translation_domain' => false,
                        'label' => 'cluster',
                        'required' => false,
                        'attr' => [
                            'size' => 10,
                        ],
                    ]);
                    break;
                case 'indices':
                    $builder->add('indices', TextareaType::class, [
                        'label' => 'indices',
                        'required' => false,
                        'constraints' => [
                            new Json(),
                        ],
                        'attr' => [
                            'data-break-after' => 'yes',
                        ],
                    ]);
                    break;
                case 'run_as':
                    $builder->add('run_as', TextareaType::class, [
                        'label' => 'run_as',
                        'required' => false,
                        'constraints' => [
                            new Json(),
                        ],
                        'attr' => [
                            'data-break-after' => 'yes',
                        ],
                    ]);
                    break;
                case 'metadata':
                    $builder->add('metadata', TextareaType::class, [
                        'label' => 'metadata',
                        'required' => false,
                        'constraints' => [
                            new Json(),
                        ],
                    ]);
                    break;
            }
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ElasticsearchRoleModel::class,
            'cluster' => [
                'all',
                'create_snapshot',
                'delegate_pki',
                'manage',
                'manage_api_key',
                'manage_ccr',
                'manage_data_frame_transforms',
                'manage_enrich',
                'manage_ilm',
                'manage_index_templates',
                'manage_ingest_pipelines',
                'manage_ml',
                'manage_oidc',
                'manage_own_api_key',
                'manage_pipeline',
                'manage_rollup',
                'manage_saml',
                'manage_security',
                'manage_slm',
                'manage_token',
                'manage_transform',
                'manage_watcher',
                'monitor',
                'monitor_data_frame_transforms',
                'monitor_ml',
                'monitor_rollup',
                'monitor_snapshot',
                'monitor_transform',
                'monitor_watcher',
                'read_ccr',
                'read_ilm',
                'read_slm',
                'transport_client',
            ],
            'update' => false,
        ]);
    }

    public function getBlockPrefix()
    {
        return 'data';
    }
}
