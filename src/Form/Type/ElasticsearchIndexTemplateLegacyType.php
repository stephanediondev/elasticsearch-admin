<?php

namespace App\Form\Type;

use App\Form\EventListener\MappingsSettingsAliasesSubscriber;
use App\Manager\CallManager;
use App\Manager\ElasticsearchIndexTemplateLegacyManager;
use App\Model\CallRequestModel;
use App\Model\ElasticsearchIndexTemplateLegacyModel;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
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

class ElasticsearchIndexTemplateLegacyType extends AbstractType
{
    public function __construct(CallManager $callManager, ElasticsearchIndexTemplateLegacyManager $elasticsearchIndexTemplateLegacyManager, TranslatorInterface $translator)
    {
        $this->callManager = $callManager;
        $this->elasticsearchIndexTemplateLegacyManager = $elasticsearchIndexTemplateLegacyManager;
        $this->translator = $translator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $fields = [];

        if ('create' == $options['context']) {
            $fields[] = 'name';
        }
        if (true === $this->callManager->hasFeature('multiple_patterns')) {
            $fields[] = 'index_patterns';
        } else {
            $fields[] = 'template';
        }
        $fields[] = 'version';
        $fields[] = 'order';
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
                        'help' => 'help_form.index_template_legacy.name',
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
                        'help' => 'help_form.index_template_legacy.index_patterns',
                        'help_html' => true,
                    ]);
                    break;
                case 'template':
                    $builder->add('template', TextType::class, [
                        'label' => 'template',
                        'required' => true,
                        'constraints' => [
                            new NotBlank(),
                        ],
                        'help' => 'help_form.index_template_legacy.template',
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
                        'help' => 'help_form.index_template_legacy.version',
                        'help_html' => true,
                    ]);
                    break;
                case 'order':
                    $builder->add('order', IntegerType::class, [
                        'label' => 'order',
                        'required' => false,
                        'attr' => [
                            'data-break-after' => 'yes',
                        ],
                        'help' => 'help_form.index_template_legacy.order',
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
                        'help' => 'help_form.index_template_legacy.settings',
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
                        'help' => 'help_form.index_template_legacy.mappings',
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
                        'help' => 'help_form.index_template_legacy.aliases',
                        'help_html' => true,
                    ]);
                    break;
            }
        }

        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) use ($options) {
            $form = $event->getForm();

            if ('create' == $options['context']) {
                if ($form->has('name') && $form->get('name')->getData()) {
                    $template = $this->elasticsearchIndexTemplateLegacyManager->getByName($form->get('name')->getData());

                    if ($template) {
                        $form->get('name')->addError(new FormError(
                            $this->translator->trans('name_already_used')
                        ));
                    }
                }
            }
        });

        $builder->addEventSubscriber(new MappingsSettingsAliasesSubscriber());
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ElasticsearchIndexTemplateLegacyModel::class,
            'context' => 'create',
        ]);
    }

    public function getBlockPrefix()
    {
        return 'data';
    }
}
