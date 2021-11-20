<?php
declare(strict_types=1);

namespace App\Form\Type;

use App\Model\ElasticsearchClusterSettingModel;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class ElasticsearchClusterSettingType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $fields = [];

        $fields[] = 'value';

        foreach ($fields as $field) {
            switch ($field) {
                case 'value':
                    $builder->add('value', TextType::class, [
                        'label' => 'value',
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
        $resolver->setDefaults([
            'data_class' => ElasticsearchClusterSettingModel::class,
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'data';
    }
}
