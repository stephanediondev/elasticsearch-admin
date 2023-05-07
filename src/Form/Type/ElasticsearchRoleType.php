<?php

declare(strict_types=1);

namespace App\Form\Type;

use App\Manager\ElasticsearchRoleManager;
use App\Model\ElasticsearchRoleModel;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Json;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Contracts\Translation\TranslatorInterface;

class ElasticsearchRoleType extends AbstractType
{
    protected ElasticsearchRoleManager $elasticsearchRoleManager;

    protected TranslatorInterface $translator;

    public function __construct(ElasticsearchRoleManager $elasticsearchRoleManager, TranslatorInterface $translator)
    {
        $this->elasticsearchRoleManager = $elasticsearchRoleManager;
        $this->translator = $translator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $fields = [];

        if ('create' == $options['context']) {
            $fields[] = 'name';
        }
        $fields[] = 'cluster';
        $fields[] = 'run_as';
        $fields[] = 'indices_json';
        $fields[] = 'applications_json';
        $fields[] = 'metadata_json';

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
                case 'indices_json':
                    $builder->add('indices_json', TextareaType::class, [
                        'label' => 'indices',
                        'required' => false,
                        'constraints' => [
                            new Json(),
                        ],
                        'help' => 'help_form.role.indices',
                        'help_html' => true,
                    ]);
                    break;
                case 'applications_json':
                    $builder->add('applications_json', TextareaType::class, [
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
                case 'metadata_json':
                    $builder->add('metadata_json', TextareaType::class, [
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

        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) use ($options) {
            $form = $event->getForm();
            $data = $event->getData();

            if ('create' == $options['context']) {
                if ($form->has('name') && $form->get('name')->getData()) {
                    $role = $this->elasticsearchRoleManager->getByName($form->get('name')->getData());

                    if ($role) {
                        $form->get('name')->addError(new FormError(
                            $this->translator->trans('name_already_used')
                        ));
                    }
                }
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ElasticsearchRoleModel::class,
            'privileges' => [],
            'users' => [],
            'context' => 'create',
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'data';
    }
}
