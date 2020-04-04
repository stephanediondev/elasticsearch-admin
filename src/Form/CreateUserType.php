<?php

namespace App\Form;

use App\Model\ElasticsearchUserModel;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Json;

class CreateUserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $fields = [];

        if (false == $options['update']) {
            $fields[] = 'username';
            $fields[] = 'password';
        }
        $fields[] = 'email';
        $fields[] = 'full_name';
        $fields[] = 'roles';
        $fields[] = 'metadata';

        foreach ($fields as $field) {
            switch ($field) {
                case 'username':
                    $builder->add('username', TextType::class, [
                        'label' => 'username',
                        'required' => true,
                        'constraints' => [
                            new NotBlank(),
                        ],
                    ]);
                    break;
                case 'password':
                    $builder->add('password', PasswordType::class, [
                        'label' => 'password',
                        'required' => true,
                        'constraints' => [
                            new NotBlank(),
                        ],
                    ]);
                    break;
                case 'email':
                    $builder->add('email', EmailType::class, [
                        'label' => 'email',
                        'required' => false,
                    ]);
                    break;
                case 'full_name':
                    $builder->add('full_name', TextType::class, [
                        'label' => 'full_name',
                        'required' => false,
                    ]);
                    break;
                case 'roles':
                    $builder->add('roles', ChoiceType::class, [
                        'multiple' => true,
                        'choices' => $options['roles'],
                        'choice_label' => function ($choice, $key, $value) use ($options) {
                            return $options['roles'][$key];
                        },
                        'choice_translation_domain' => false,
                        'label' => 'roles',
                        'required' => false,
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
            'data_class' => ElasticsearchUserModel::class,
            'roles' => [],
            'update' => false,
        ]);
    }

    public function getBlockPrefix()
    {
        return 'data';
    }
}
