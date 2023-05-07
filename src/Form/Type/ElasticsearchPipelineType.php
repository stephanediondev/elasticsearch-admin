<?php

declare(strict_types=1);

namespace App\Form\Type;

use App\Manager\ElasticsearchPipelineManager;
use App\Model\ElasticsearchPipelineModel;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use Symfony\Component\Validator\Constraints\Json;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Contracts\Translation\TranslatorInterface;

class ElasticsearchPipelineType extends AbstractType
{
    protected ElasticsearchPipelineManager $elasticsearchPipelineManager;

    protected TranslatorInterface $translator;

    public function __construct(ElasticsearchPipelineManager $elasticsearchPipelineManager, TranslatorInterface $translator)
    {
        $this->elasticsearchPipelineManager = $elasticsearchPipelineManager;
        $this->translator = $translator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $fields = [];

        if ('create' == $options['context']) {
            $fields[] = 'name';
        }
        $fields[] = 'description';
        $fields[] = 'version';
        $fields[] = 'processors_json';
        $fields[] = 'on_failure_json';

        foreach ($fields as $field) {
            switch ($field) {
                case 'name':
                    $builder->add('name', TextType::class, [
                        'label' => 'name',
                        'required' => true,
                        'constraints' => [
                            new NotBlank(),
                        ],
                        'help' => 'help_form.pipeline.name',
                        'help_html' => true,
                    ]);
                    break;
                case 'description':
                    $builder->add('description', TextType::class, [
                        'label' => 'description',
                        'required' => false,
                        'help' => 'help_form.pipeline.description',
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
                    ]);
                    break;
                case 'processors_json':
                    $builder->add('processors_json', TextareaType::class, [
                        'label' => 'processors',
                        'required' => true,
                        'constraints' => [
                            new NotBlank(),
                            new Json(),
                        ],
                        'attr' => [
                            'data-break-after' => 'yes',
                        ],
                        'help' => 'help_form.pipeline.processors',
                        'help_html' => true,
                    ]);
                    break;
                case 'on_failure_json':
                    $builder->add('on_failure_json', TextareaType::class, [
                        'label' => 'on_failure',
                        'required' => false,
                        'constraints' => [
                            new Json(),
                        ],
                        'help' => 'help_form.pipeline.on_failure',
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
                    $pipeline = $this->elasticsearchPipelineManager->getByName($form->get('name')->getData());

                    if ($pipeline) {
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
            'data_class' => ElasticsearchPipelineModel::class,
            'context' => 'create',
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'data';
    }
}
