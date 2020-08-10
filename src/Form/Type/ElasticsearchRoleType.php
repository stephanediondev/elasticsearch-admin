<?php

namespace App\Form\Type;

use App\Manager\ElasticsearchRoleManager;
use App\Model\CallRequestModel;
use App\Model\ElasticsearchRoleModel;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormError;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Json;
use Symfony\Contracts\Translation\TranslatorInterface;

class ElasticsearchRoleType extends AbstractType
{
    public function __construct(ElasticsearchRoleManager $elasticsearchRoleManager, TranslatorInterface $translator)
    {
        $this->elasticsearchRoleManager = $elasticsearchRoleManager;
        $this->translator = $translator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $fields = [];

        if ('create' == $options['context']) {
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

        $builder->addEventListener(FormEvents::POST_SET_DATA, function (FormEvent $event) {
            $form = $event->getForm();

            if ($form->has('indices') && $form->get('indices')->getData()) {
                $fieldOptions = $form->get('indices')->getConfig()->getOptions();
                $fieldOptions['data'] = json_encode($form->get('indices')->getData(), JSON_PRETTY_PRINT);
                $form->add('indices', TextareaType::class, $fieldOptions);
            }

            if ($form->has('applications') && $form->get('applications')->getData()) {
                $fieldOptions = $form->get('applications')->getConfig()->getOptions();
                $fieldOptions['data'] = json_encode($form->get('applications')->getData(), JSON_PRETTY_PRINT);
                $form->add('applications', TextareaType::class, $fieldOptions);
            }

            if ($form->has('metadata') && $form->get('metadata')->getData()) {
                $fieldOptions = $form->get('metadata')->getConfig()->getOptions();
                $fieldOptions['data'] = json_encode($form->get('metadata')->getData(), JSON_PRETTY_PRINT);
                $form->add('metadata', TextareaType::class, $fieldOptions);
            }
        });

        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) use ($options) {
            $form = $event->getForm();

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

            if ($form->has('indices') && $form->get('indices')->getData()) {
                $role = $event->getData();
                $role->setIndices(json_decode($form->get('indices')->getData(), true));
                $event->setData($role);
            }

            if ($form->has('applications') && $form->get('applications')->getData()) {
                $role = $event->getData();
                $role->setApplications(json_decode($form->get('applications')->getData(), true));
                $event->setData($role);
            }

            if ($form->has('metadata') && $form->get('metadata')->getData()) {
                $role = $event->getData();
                $role->setMetadata(json_decode($form->get('metadata')->getData(), true));
                $event->setData($role);
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ElasticsearchRoleModel::class,
            'privileges' => [],
            'users' => [],
            'context' => 'create',
        ]);
    }

    public function getBlockPrefix()
    {
        return 'data';
    }
}
