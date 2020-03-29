<?php

namespace App\Form;

use App\Model\ElasticsearchRepositoryFsModel;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class CreateRepositoryFsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $fields = [];

        $fields[] = 'name';
        $fields[] = 'location';
        $fields[] = 'chunk_size';
        $fields[] = 'max_restore_bytes_per_sec';
        $fields[] = 'max_snapshot_bytes_per_sec';
        $fields[] = 'compress';
        $fields[] = 'readonly';

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
                case 'location':
                    $builder->add('location', TextType::class, [
                        'label' => 'location',
                        'required' => true,
                        'constraints' => [
                            new NotBlank(),
                        ],
                        'attr' => [
                            'data-break-after' => 'yes',
                        ],
                        'help' => 'Location of the snapshots. Mandatory. This location (or one of its parent directories) must be registered in the path.repo setting on all master and data nodes.',
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
                    ]);
                    break;
            }
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ElasticsearchRepositoryFsModel::class,
        ]);
    }

    public function getBlockPrefix()
    {
        return 'data';
    }
}
