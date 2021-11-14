<?php

namespace App\Form\Type;

use App\Manager\CallManager;
use App\Manager\ElasticsearchEnrichPolicyManager;
use App\Model\CallRequestModel;
use App\Model\ElasticsearchEnrichPolicyModel;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormError;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Form\FormInterface;

class ElasticsearchEnrichPolicyType extends AbstractType
{
    protected ElasticsearchEnrichPolicyManager $elasticsearchEnrichPolicyManager;

    protected CallManager $callManager;

    protected TranslatorInterface $translator;

    public function __construct(ElasticsearchEnrichPolicyManager $elasticsearchEnrichPolicyManager, CallManager $callManager, TranslatorInterface $translator)
    {
        $this->elasticsearchEnrichPolicyManager = $elasticsearchEnrichPolicyManager;
        $this->callManager = $callManager;
        $this->translator = $translator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $fields = [];

        if ('create' == $options['context']) {
            $fields[] = 'name';
        }
        $fields[] = 'type';
        $fields[] = 'indices';
        $fields[] = 'match_field';
        $fields[] = 'enrich_fields';
        $fields[] = 'query';

        foreach ($fields as $field) {
            switch ($field) {
                case 'name':
                    $builder->add('name', TextType::class, [
                        'label' => 'name',
                        'required' => true,
                        'constraints' => [
                            new NotBlank(),
                        ],
                        'help' => 'help_form.enrich_policy.name',
                        'help_html' => true,
                    ]);
                    break;
                case 'type':
                    $builder->add('type', ChoiceType::class, [
                        'placeholder' => '-',
                        'choices' => ElasticsearchEnrichPolicyModel::getTypes(),
                        'choice_label' => function ($choice, $key, $value) {
                            return $key;
                        },
                        'choice_translation_domain' => false,
                        'label' => 'type',
                        'required' => true,
                        'constraints' => [
                            new NotBlank(),
                        ],
                        'help' => 'help_form.enrich_policy.type',
                        'help_html' => true,
                    ]);
                    break;
                case 'indices':
                    $builder->add('indices', ChoiceType::class, [
                        'multiple' => true,
                        'choices' => $options['indices'],
                        'choice_label' => function ($choice, $key, $value) use ($options) {
                            return $options['indices'][$key];
                        },
                        'choice_translation_domain' => false,
                        'label' => 'indices',
                        'required' => true,
                        'constraints' => [
                            new NotBlank(),
                        ],
                        'attr' => [
                            'data-break-after' => 'yes',
                        ],
                        'help' => 'help_form.enrich_policy.indices',
                        'help_html' => true,
                    ]);
                    break;
            }
        }

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $form = $event->getForm();
            $data = $event->getData();

            $this->enrichFields($form, $data->getIndices(), $data->getEnrichFields());
        });

        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
            $form = $event->getForm();
            $data = $event->getData();

            $this->enrichFields($form, $data['indices'], []);
        });

        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) use ($options) {
            $form = $event->getForm();

            if ('create' == $options['context']) {
                if ($form->has('name') && $form->get('name')->getData()) {
                    $policy = $this->elasticsearchEnrichPolicyManager->getByName($form->get('name')->getData());

                    if ($policy) {
                        $form->get('name')->addError(new FormError(
                            $this->translator->trans('name_already_used')
                        ));
                    }
                }
            }
        });
    }

    private function enrichFields(FormInterface $form, ?array $indices, ?array $selected): void
    {
        $choices = [];

        if ($indices && 0 < count($indices)) {
            $callRequest = new CallRequestModel();
            $callRequest->setPath('/'.implode(',', $indices).'/_mapping');
            $callResponse = $this->callManager->call($callRequest);

            $results = $callResponse->getContent();

            foreach ($results as $result) {
                if (true === isset($result['mappings']) && true === isset($result['mappings']['properties'])) {
                    foreach ($result['mappings']['properties'] as $k => $property) {
                        $choices[$k] = $k;
                    }
                }
            }
        }

        $form->add('match_field', ChoiceType::class, [
            'placeholder' => '-',
            'choices' => $choices,
            'choice_label' => function ($choice, $key, $value) {
                return $choice;
            },
            'choice_translation_domain' => false,
            'label' => 'match_field',
            'required' => true,
            'constraints' => [
                new NotBlank(),
            ],
            'attr' => [
                'class' => 'update-fields',
            ],
            'help' => 'help_form.enrich_policy.match_field',
            'help_html' => true,
        ]);

        $form->add('enrich_fields', ChoiceType::class, [
            'data' => $selected,
            'multiple' => true,
            'choices' => $choices,
            'choice_label' => function ($choice, $key, $value) {
                return $choice;
            },
            'choice_translation_domain' => false,
            'label' => 'enrich_fields',
            'required' => true,
            'constraints' => [
                new NotBlank(),
            ],
            'attr' => [
                'class' => 'update-fields',
                'data-break-after' => 'yes',
            ],
            'help' => 'help_form.enrich_policy.enrich_fields',
            'help_html' => true,
        ]);

        $form->add('query', TextType::class, [
            'label' => 'query',
            'required' => false,
            'help' => 'help_form.enrich_policy.query',
            'help_html' => true,
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ElasticsearchEnrichPolicyModel::class,
            'indices' => [],
            'context' => 'create',
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'data';
    }
}
