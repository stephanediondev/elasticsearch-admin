<?php
declare(strict_types=1);

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class ElasticsearchIndexImportType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $fields = [];

        $fields[] = 'import_file';

        foreach ($fields as $field) {
            switch ($field) {
                case 'import_file':
                    $builder->add('import_file', FileType::class, [
                        'label' => 'import_file',
                        'required' => true,
                        'constraints' => [
                            new NotBlank(),
                        ],

                    ]);
                    break;
            }
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
    }

    public function getBlockPrefix(): string
    {
        return 'data';
    }
}
