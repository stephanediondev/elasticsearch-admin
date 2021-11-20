<?php
declare(strict_types=1);

namespace App\Form\Type;

use App\Model\ElasticsearchReloadSecureSettingsModel;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ElasticsearchNodeReloadSecureSettingsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $fields = [];

        $fields[] = 'secure_settings_password';

        foreach ($fields as $field) {
            switch ($field) {
                case 'secure_settings_password':
                    $builder->add('secure_settings_password', PasswordType::class, [
                        'label' => 'secure_settings_password',
                        'required' => false,
                    ]);
                    break;
            }
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ElasticsearchReloadSecureSettingsModel::class,
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'data';
    }
}
