<?php

namespace App\Form;

use App\Manager\ElasticsearchComponentTemplateManager;
use App\Model\CallRequestModel;
use App\Model\ElasticsearchComponentTemplateModel;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormError;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Json;
use Symfony\Contracts\Translation\TranslatorInterface;

class CreateComponentTemplateType extends AbstractType
{
    public function __construct(ElasticsearchComponentTemplateManager $elasticsearchComponentTemplateManager, TranslatorInterface $translator)
    {
        $this->elasticsearchComponentTemplateManager = $elasticsearchComponentTemplateManager;
        $this->translator = $translator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $fields = [];

        if ('create' == $options['context']) {
            $fields[] = 'name';
        }
        $fields[] = 'version';
        $fields[] = 'settings';
        $fields[] = 'mappings';
        $fields[] = 'aliases';

        foreach ($fields as $field) {
            switch ($field) {
                case 'name':
                    $builder->add('name', TextType::class, [
                        'label' => 'name',
                        'required' => true,
                        'constraints' => [
                            new NotBlank(),
                        ],
                        'help' => 'help_form.component_template.name',
                        'help_html' => true,
                    ]);
                    break;
                case 'version':
                    $builder->add('version', IntegerType::class, [
                        'label' => 'version',
                        'required' => false,
                        'constraints' => [
                            new GreaterThanOrEqual(1),
                        ],
                        'attr' => [
                            'min' => 1,
                        ],
                        'help' => 'help_form.component_template.version',
                        'help_html' => true,
                    ]);
                    break;
                case 'settings':
                    $builder->add('settings', TextareaType::class, [
                        'label' => 'settings',
                        'required' => false,
                        'constraints' => [
                            new Json(),
                        ],
                        'attr' => [
                            'data-break-after' => 'yes',
                        ],
                        'help' => 'help_form.component_template.settings',
                        'help_html' => true,
                    ]);
                    break;
                case 'mappings':
                    $builder->add('mappings', TextareaType::class, [
                        'label' => 'mappings',
                        'required' => false,
                        'constraints' => [
                            new Json(),
                        ],
                        'help' => 'help_form.component_template.mappings',
                        'help_html' => true,
                    ]);
                    break;
                case 'aliases':
                    $builder->add('aliases', TextareaType::class, [
                        'label' => 'aliases',
                        'required' => false,
                        'constraints' => [
                            new Json(),
                        ],
                        'help' => 'help_form.component_template.aliases',
                        'help_html' => true,
                    ]);
                    break;
            }
        }

        $builder->addEventListener(FormEvents::POST_SET_DATA, function (FormEvent $event) use ($options) {
            $form = $event->getForm();

            if ($form->has('mappings') && $form->get('mappings')->getData()) {
                $fieldOptions = $form->get('mappings')->getConfig()->getOptions();
                $fieldOptions['data'] = json_encode($form->get('mappings')->getData(), JSON_PRETTY_PRINT);
                $form->add('mappings', TextareaType::class, $fieldOptions);
            }

            if ($form->has('settings') && $form->get('settings')->getData()) {
                $fieldOptions = $form->get('settings')->getConfig()->getOptions();
                $fieldOptions['data'] = json_encode($form->get('settings')->getData(), JSON_PRETTY_PRINT);
                $form->add('settings', TextareaType::class, $fieldOptions);
            }

            if ($form->has('aliases') && $form->get('aliases')->getData()) {
                $fieldOptions = $form->get('aliases')->getConfig()->getOptions();
                $fieldOptions['data'] = json_encode($form->get('aliases')->getData(), JSON_PRETTY_PRINT);
                $form->add('aliases', TextareaType::class, $fieldOptions);
            }
        });

        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) use ($options) {
            $form = $event->getForm();

            if ('create' == $options['context']) {
                if ($form->has('name') && $form->get('name')->getData()) {
                    $template = $this->elasticsearchComponentTemplateManager->getByName($form->get('name')->getData());

                    if ($template) {
                        $form->get('name')->addError(new FormError(
                            $this->translator->trans('name_already_used')
                        ));
                    }
                }
            }

            if ($form->has('mappings') && $form->get('mappings')->getData()) {
                $template = $event->getData();
                $template->setMappings(json_decode($form->get('mappings')->getData(), true));
                $event->setData($template);
            }

            if ($form->has('settings') && $form->get('settings')->getData()) {
                $template = $event->getData();
                $template->setSettings(json_decode($form->get('settings')->getData(), true));
                $event->setData($template);
            }

            if ($form->has('aliases') && $form->get('aliases')->getData()) {
                $template = $event->getData();
                $template->setAliases(json_decode($form->get('aliases')->getData(), true));
                $event->setData($template);
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ElasticsearchComponentTemplateModel::class,
            'context' => 'create',
        ]);
    }

    public function getBlockPrefix()
    {
        return 'data';
    }
}
