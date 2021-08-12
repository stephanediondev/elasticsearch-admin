<?php

namespace App\Form\Type;

use App\Manager\ElasticsearchRepositoryManager;
use App\Model\CallRequestModel;
use App\Model\ElasticsearchRepositoryModel;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormError;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Contracts\Translation\TranslatorInterface;

class ElasticsearchRepositoryType extends AbstractType
{
    protected $elasticsearchRepositoryManager;

    protected $translator;

    public function __construct(ElasticsearchRepositoryManager $elasticsearchRepositoryManager, TranslatorInterface $translator)
    {
        $this->elasticsearchRepositoryManager = $elasticsearchRepositoryManager;
        $this->translator = $translator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $fields = [];

        if ('create' == $options['context']) {
            $fields[] = 'name';
        }
        $fields[] = 'chunk_size';
        $fields[] = 'max_restore_bytes_per_sec';
        $fields[] = 'max_snapshot_bytes_per_sec';
        $fields[] = 'compress';
        $fields[] = 'readonly';
        $fields[] = 'verify';

        if (ElasticsearchRepositoryModel::TYPE_FS == $options['type']) {
            $fields[] = 'location';
        }

        if (ElasticsearchRepositoryModel::TYPE_S3 == $options['type']) {
            $fields[] = 'bucket';
            $fields[] = 'client';
            $fields[] = 'base_path';
            $fields[] = 'server_side_encryption';
            $fields[] = 'buffer_size';
            $fields[] = 'canned_acl';
            $fields[] = 'storage_class';
        }

        if (ElasticsearchRepositoryModel::TYPE_GCS == $options['type']) {
            $fields[] = 'bucket';
            $fields[] = 'client';
            $fields[] = 'base_path';
        }

        if (ElasticsearchRepositoryModel::TYPE_AZURE == $options['type']) {
            $fields[] = 'container';
            $fields[] = 'client';
            $fields[] = 'base_path';
            $fields[] = 'location_mode';
        }

        foreach ($fields as $field) {
            switch ($field) {
                case 'name':
                    $builder->add('name', TextType::class, [
                        'label' => 'name',
                        'required' => true,
                        'constraints' => [
                            new NotBlank(),
                        ],
                        'help' => 'help_form.repository.name',
                        'help_html' => true,
                    ]);
                    break;
                case 'compress':
                    $builder->add('compress', CheckboxType::class, [
                        'label' => 'compress',
                        'required' => false,
                        'help' => 'help_form.repository.compress',
                        'help_html' => true,
                    ]);
                    break;
                case 'chunk_size':
                    $builder->add('chunk_size', TextType::class, [
                        'label' => 'chunk_size',
                        'required' => false,
                        'help' => 'help_form.repository.chunk_size',
                        'help_html' => true,
                    ]);
                    break;
                case 'max_restore_bytes_per_sec':
                    $builder->add('max_restore_bytes_per_sec', TextType::class, [
                        'label' => 'max_restore_bytes_per_sec',
                        'required' => false,
                        'help' => 'help_form.repository.max_restore_bytes_per_sec',
                        'help_html' => true,
                    ]);
                    break;
                case 'max_snapshot_bytes_per_sec':
                    $builder->add('max_snapshot_bytes_per_sec', TextType::class, [
                        'label' => 'max_snapshot_bytes_per_sec',
                        'required' => false,
                        'help' => 'help_form.repository.max_snapshot_bytes_per_sec',
                        'help_html' => true,
                    ]);
                    break;
                case 'readonly':
                    $builder->add('readonly', CheckboxType::class, [
                        'label' => 'readonly',
                        'required' => false,
                        'help' => 'help_form.repository.readonly',
                        'help_html' => true,
                    ]);
                    break;
                case 'verify':
                    $builder->add('verify', CheckboxType::class, [
                        'label' => 'verify',
                        'required' => false,
                        'help' => 'help_form.repository.verify',
                        'help_html' => true,
                        'attr' => [
                            'data-break-after' => 'yes',
                        ],
                    ]);
                    break;
                case 'location':
                    $builder->add('location', ChoiceType::class, [
                        'placeholder' => '-',
                        'choices' => $options['paths'],
                        'choice_label' => function ($choice, $key, $value) {
                            return $value;
                        },
                        'choice_translation_domain' => false,
                        'label' => 'location',
                        'required' => true,
                        'constraints' => [
                            new NotBlank(),
                        ],
                        'help' => 'help_form.repository.fs.location',
                        'help_html' => true,
                    ]);
                    break;
                case 'bucket':
                    $builder->add('bucket', TextType::class, [
                        'label' => 'bucket',
                        'required' => true,
                        'constraints' => [
                            new NotBlank(),
                        ],
                        'help' => 'help_form.repository.'.$options['type'].'.bucket',
                        'help_html' => true,
                    ]);
                    break;
                case 'container':
                    $builder->add('container', TextType::class, [
                        'label' => 'container',
                        'required' => true,
                        'constraints' => [
                            new NotBlank(),
                        ],
                        'help' => 'help_form.repository.'.$options['type'].'.container',
                        'help_html' => true,
                    ]);
                    break;
                case 'client':
                    $builder->add('client', TextType::class, [
                        'label' => 'client',
                        'required' => true,
                        'constraints' => [
                            new NotBlank(),
                        ],
                        'help' => 'help_form.repository.'.$options['type'].'.client',
                        'help_html' => true,
                    ]);
                    break;
                case 'base_path':
                    $builder->add('base_path', TextType::class, [
                        'label' => 'base_path',
                        'required' => false,
                        'help' => 'help_form.repository.'.$options['type'].'.base_path',
                        'help_html' => true,
                    ]);
                    break;
                case 'server_side_encryption':
                    $builder->add('server_side_encryption', CheckboxType::class, [
                        'label' => 'server_side_encryption',
                        'required' => false,
                        'help' => 'help_form.repository.s3.server_side_encryption',
                        'help_html' => true,
                    ]);
                    break;
                case 'buffer_size':
                    $builder->add('buffer_size', TextType::class, [
                        'label' => 'buffer_size',
                        'required' => false,
                        'help' => 'help_form.repository.s3.buffer_size',
                        'help_html' => true,
                    ]);
                    break;
                case 'canned_acl':
                    $builder->add('canned_acl', ChoiceType::class, [
                        'choices' => ElasticsearchRepositoryModel::cannedAcls(),
                        'choice_label' => function ($choice, $key, $value) {
                            return $key;
                        },
                        'choice_translation_domain' => false,
                        'label' => 'canned_acl',
                        'required' => true,
                        'constraints' => [
                            new NotBlank(),
                        ],
                        'help' => 'help_form.repository.s3.canned_acl',
                        'help_html' => true,
                    ]);
                    break;
                case 'storage_class':
                    $builder->add('storage_class', ChoiceType::class, [
                        'choices' => ElasticsearchRepositoryModel::storageClasses(),
                        'choice_label' => function ($choice, $key, $value) {
                            return $key;
                        },
                        'choice_translation_domain' => false,
                        'label' => 'storage_class',
                        'required' => true,
                        'constraints' => [
                            new NotBlank(),
                        ],
                        'help' => 'help_form.repository.s3.storage_class',
                        'help_html' => true,
                    ]);
                    break;
                case 'location_mode':
                    $builder->add('location_mode', ChoiceType::class, [
                        'choices' => ElasticsearchRepositoryModel::locationModes(),
                        'choice_label' => function ($choice, $key, $value) {
                            return $key;
                        },
                        'choice_translation_domain' => false,
                        'label' => 'location_mode',
                        'required' => true,
                        'constraints' => [
                            new NotBlank(),
                        ],
                        'help' => 'help_form.repository.azure.location_mode',
                        'help_html' => true,
                    ]);
                    break;
            }
        }

        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) use ($options) {
            $form = $event->getForm();

            if ('create' == $options['context']) {
                if ($form->has('name') && $form->get('name')->getData()) {
                    $repository = $this->elasticsearchRepositoryManager->getByName($form->get('name')->getData());

                    if ($repository) {
                        $form->get('name')->addError(new FormError(
                            $this->translator->trans('name_already_used')
                        ));
                    }
                }
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ElasticsearchRepositoryModel::class,
            'type' => false,
            'context' => 'create',
            'paths' => [],
        ]);
    }

    public function getBlockPrefix()
    {
        return 'data';
    }
}
