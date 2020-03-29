<?php

namespace App\Form;

use App\Model\ElasticsearchRepositoryModel;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class CreateRepositoryType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $fields = [];

        if (false == $options['update']) {
            $fields[] = 'name';
        }
        $fields[] = 'chunk_size';
        $fields[] = 'max_restore_bytes_per_sec';
        $fields[] = 'max_snapshot_bytes_per_sec';
        $fields[] = 'compress';
        $fields[] = 'readonly';

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
                        'attr' => [
                            'data-break-after' => 'yes',
                        ],
                    ]);
                    break;
                case 'location':
                    $builder->add('location', TextType::class, [
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
                        'help' => 'help_form.repository.s3.bucket',
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
                        'help' => 'help_form.repository.s3.client',
                        'help_html' => true,
                    ]);
                    break;
                case 'base_path':
                    $builder->add('base_path', TextType::class, [
                        'label' => 'base_path',
                        'required' => false,
                        'help' => 'help_form.repository.s3.base_path',
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
            }
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ElasticsearchRepositoryModel::class,
            'type' => false,
            'update' => false,
        ]);
    }

    public function getBlockPrefix()
    {
        return 'data';
    }
}
