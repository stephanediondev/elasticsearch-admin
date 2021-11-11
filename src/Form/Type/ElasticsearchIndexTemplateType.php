<?php

namespace App\Form\Type;

use App\Form\EventListener\MappingsSettingsAliasesSubscriber;
use App\Form\EventListener\MetadataSubscriber;
use App\Manager\CallManager;
use App\Manager\ElasticsearchIndexTemplateManager;
use App\Model\CallRequestModel;
use App\Model\ElasticsearchIndexTemplateModel;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormError;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Json;
use Symfony\Contracts\Translation\TranslatorInterface;

class ElasticsearchIndexTemplateType extends AbstractType
{
    protected CallManager $callManager;

    protected ElasticsearchIndexTemplateManager $elasticsearchIndexTemplateManager;

    protected TranslatorInterface $translator;

    public function __construct(CallManager $callManager, ElasticsearchIndexTemplateManager $elasticsearchIndexTemplateManager, TranslatorInterface $translator)
    {
        $this->callManager = $callManager;
        $this->elasticsearchIndexTemplateManager = $elasticsearchIndexTemplateManager;
        $this->translator = $translator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $fields = [];

        if ('create' == $options['context']) {
            $fields[] = 'name';
        }
        $fields[] = 'index_patterns';
        $fields[] = 'version';
        $fields[] = 'priority';
        if (true === $this->callManager->hasFeature('data_streams')) {
            $fields[] = 'data_stream';
        }
        $fields[] = 'composed_of';
        $fields[] = 'settings';
        $fields[] = 'mappings';
        $fields[] = 'aliases';
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
                        'help' => 'help_form.index_template.name',
                        'help_html' => true,
                    ]);
                    break;
                case 'index_patterns':
                    $builder->add('index_patterns', TextType::class, [
                        'label' => 'index_patterns',
                        'required' => true,
                        'constraints' => [
                            new NotBlank(),
                        ],
                        'help' => 'help_form.index_template.index_patterns',
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
                        'help' => 'help_form.index_template.version',
                        'help_html' => true,
                    ]);
                    break;
                case 'priority':
                    $builder->add('priority', IntegerType::class, [
                        'label' => 'priority',
                        'required' => false,
                        'help' => 'help_form.index_template.priority',
                        'help_html' => true,
                    ]);
                    break;
                case 'composed_of':
                    $builder->add('composed_of', ChoiceType::class, [
                        'multiple' => true,
                        'choices' => $options['component_templates'],
                        'choice_label' => function ($choice, $key, $value) use ($options) {
                            return $options['component_templates'][$key];
                        },
                        'choice_translation_domain' => false,
                        'label' => 'composed_of',
                        'required' => false,
                        'attr' => [
                            'data-break-after' => 'yes',
                        ],
                        'help' => 'help_form.index_template.composed_of',
                        'help_html' => true,
                    ]);
                    break;
                case 'data_stream':
                    $builder->add('data_stream', CheckboxType::class, [
                        'label' => 'data_stream',
                        'required' => false,
                        'help' => 'help_form.index_template.data_stream',
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
                        'help' => 'help_form.index_template.settings',
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
                        'attr' => [
                            'data-break-after' => 'yes',
                        ],
                        'help' => 'help_form.index_template.mappings',
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
                        'help' => 'help_form.index_template.aliases',
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
                        'help' => 'help_form.index_template.metadata',
                        'help_html' => true,
                    ]);
                    break;
            }
        }

        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) use ($options) {
            $form = $event->getForm();

            if ('create' == $options['context']) {
                if ($form->has('name') && $form->get('name')->getData()) {
                    $template = $this->elasticsearchIndexTemplateManager->getByName($form->get('name')->getData());

                    if ($template) {
                        $form->get('name')->addError(new FormError(
                            $this->translator->trans('name_already_used')
                        ));
                    }
                }
            }
        });

        $builder->addEventSubscriber(new MappingsSettingsAliasesSubscriber());
        $builder->addEventSubscriber(new MetadataSubscriber());
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ElasticsearchIndexTemplateModel::class,
            'component_templates' => [],
            'context' => 'create',
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'data';
    }
}
