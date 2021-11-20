<?php
declare(strict_types=1);

namespace App\Form\Type;

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

class ElasticsearchComponentTemplateType extends AbstractType
{
    protected ElasticsearchComponentTemplateManager $elasticsearchComponentTemplateManager;

    protected TranslatorInterface $translator;

    public function __construct(ElasticsearchComponentTemplateManager $elasticsearchComponentTemplateManager, TranslatorInterface $translator)
    {
        $this->elasticsearchComponentTemplateManager = $elasticsearchComponentTemplateManager;
        $this->translator = $translator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $fields = [];

        if ('create' == $options['context']) {
            $fields[] = 'name';
        }
        $fields[] = 'version';
        $fields[] = 'settings_json';
        $fields[] = 'mappings_json';
        $fields[] = 'aliases_json';
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
                            'data-break-after' => 'yes',
                            'min' => 1,
                        ],
                        'help' => 'help_form.component_template.version',
                        'help_html' => true,
                    ]);
                    break;
                case 'settings_json':
                    $builder->add('settings_json', TextareaType::class, [
                        'label' => 'settings',
                        'required' => false,
                        'constraints' => [
                            new Json(),
                        ],
                        'help' => 'help_form.component_template.settings',
                        'help_html' => true,
                    ]);
                    break;
                case 'mappings_json':
                    $builder->add('mappings_json', TextareaType::class, [
                        'label' => 'mappings',
                        'required' => false,
                        'constraints' => [
                            new Json(),
                        ],
                        'attr' => [
                            'data-break-after' => 'yes',
                        ],
                        'help' => 'help_form.component_template.mappings',
                        'help_html' => true,
                    ]);
                    break;
                case 'aliases_json':
                    $builder->add('aliases_json', TextareaType::class, [
                        'label' => 'aliases',
                        'required' => false,
                        'constraints' => [
                            new Json(),
                        ],
                        'help' => 'help_form.component_template.aliases',
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
                        'help' => 'help_form.component_template.metadata',
                        'help_html' => true,
                    ]);
                    break;
            }
        }

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
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ElasticsearchComponentTemplateModel::class,
            'context' => 'create',
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'data';
    }
}
