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
        $fields[] = 'run_as';
        $fields[] = 'indices';
        $fields[] = 'applications';
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
                        'help' => 'help_form.role.name',
                        'help_html' => true,
                    ]);
                    break;
                case 'cluster':
                    $builder->add('cluster', ChoiceType::class, [
                        'multiple' => true,
                        'choices' => $options['privileges']['cluster'],
                        'choice_label' => function ($choice, $key, $value) use ($options) {
                            return $options['privileges']['cluster'][$key];
                        },
                        'choice_translation_domain' => false,
                        'label' => 'cluster',
                        'required' => false,
                        'help' => 'help_form.role.cluster',
                        'help_html' => true,
                    ]);
                    break;
                case 'run_as':
                    $builder->add('run_as', ChoiceType::class, [
                        'multiple' => true,
                        'choices' => $options['users'],
                        'choice_label' => function ($choice, $key, $value) use ($options) {
                            return $options['users'][$key];
                        },
                        'choice_translation_domain' => false,
                        'label' => 'run_as',
                        'required' => false,
                        'attr' => [
                            'data-break-after' => 'yes',
                        ],
                        'help' => 'help_form.role.run_as',
                        'help_html' => true,
                    ]);
                    break;
                case 'indices':
                    $builder->add('indices', TextareaType::class, [
                        'label' => 'indices',
                        'required' => false,
                        'constraints' => [
                            new Json(),
                        ],
                        'help' => 'help_form.role.indices',
                        'help_html' => true,
                    ]);
                    break;
                case 'applications':
                    $builder->add('applications', TextareaType::class, [
                        'label' => 'applications',
                        'required' => false,
                        'constraints' => [
                            new Json(),
                        ],
                        'attr' => [
                            'data-break-after' => 'yes',
                        ],
                        'help' => 'help_form.role.applications',
                        'help_html' => true,
                    ]);
                    break;
                case 'metadata':
                    $builder->add('metadata', TextareaType::class, [
                        'label' => 'metadata',
                        'required' => false,
                        'constraints' => [
                            new Json(),
                        ],
                        'help' => 'help_form.role.metadata',
                        'help_html' => true,
                    ]);
                    break;
            }
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ElasticsearchRoleModel::class,
            'privileges' => [],
            'users' => [],
            'update' => false,
        ]);
    }

    public function getBlockPrefix()
    {
        return 'data';
    }
}
