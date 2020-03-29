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
                        'help' => 'Repository name. Mandatory.',
                    ]);
                    break;
                case 'compress':
                    $builder->add('compress', CheckboxType::class, [
                        'label' => 'compress',
                        'required' => false,
                        'help' => 'Turns on compression of the snapshot files. Compression is applied only to metadata files (index mapping and settings). Data files are not compressed. Defaults to true.',
                    ]);
                    break;
                case 'chunk_size':
                    $builder->add('chunk_size', TextType::class, [
                        'label' => 'chunk_size',
                        'required' => false,
                        'help' => 'Big files can be broken down into chunks during snapshotting if needed. Specify the chunk size as a value and unit, for example: 1GB, 10MB, 5KB, 500B. Defaults to null (unlimited chunk size).',
                    ]);
                    break;
                case 'max_restore_bytes_per_sec':
                    $builder->add('max_restore_bytes_per_sec', TextType::class, [
                        'label' => 'max_restore_bytes_per_sec',
                        'required' => false,
                        'help' => 'Throttles per node restore rate. Defaults to 40mb per second.',
                    ]);
                    break;
                case 'max_snapshot_bytes_per_sec':
                    $builder->add('max_snapshot_bytes_per_sec', TextType::class, [
                        'label' => 'max_snapshot_bytes_per_sec',
                        'required' => false,
                        'help' => 'Throttles per node snapshot rate. Defaults to 40mb per second.',
                    ]);
                    break;
                case 'readonly':
                    $builder->add('readonly', CheckboxType::class, [
                        'label' => 'readonly',
                        'required' => false,
                        'help' => 'Makes repository read-only. Defaults to false.',
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
                        'help' => 'Location of the snapshots. Mandatory. This location (or one of its parent directories) must be registered in the path.repo setting on all master and data nodes.',
                    ]);
                    break;
                case 'bucket':
                    $builder->add('bucket', TextType::class, [
                        'label' => 'bucket',
                        'required' => true,
                        'constraints' => [
                            new NotBlank(),
                        ],
                        'help' => 'The name of the bucket to be used for snapshots. Mandatory.',
                    ]);
                    break;
                case 'client':
                    $builder->add('client', TextType::class, [
                        'label' => 'client',
                        'required' => true,
                        'constraints' => [
                            new NotBlank(),
                        ],
                        'help' => 'The name of the S3 client to use to connect to S3. Defaults to default.',
                    ]);
                    break;
                case 'base_path':
                    $builder->add('base_path', TextType::class, [
                        'label' => 'base_path',
                        'required' => false,
                        'help' => 'Specifies the path within bucket to repository data. Defaults to value of repositories.s3.base_path or to root directory if not set. Previously, the base_path could take a leading / (forward slash). However, this has been deprecated and setting the base_path now should omit the leading /.',
                    ]);
                    break;
                case 'server_side_encryption':
                    $builder->add('server_side_encryption', CheckboxType::class, [
                        'label' => 'server_side_encryption',
                        'required' => false,
                        'help' => 'Encrypts files on the server using AES256 algorithm.',
                    ]);
                    break;
                case 'buffer_size':
                    $builder->add('buffer_size', TextType::class, [
                        'label' => 'buffer_size',
                        'required' => false,
                        'help' => 'Beyond this minimum threshold, the S3 repository will use the AWS Multipart Upload API to split the chunk into several parts and upload each in its own request.',
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
                        'help' => 'The canned ACL to add to new S3 buckets and objects.',
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
                        'help' => 'The storage class for new objects in the S3 repository.',
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
