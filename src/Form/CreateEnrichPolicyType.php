<?php

namespace App\Form;

use App\Manager\CallManager;
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

class CreateEnrichPolicyType extends AbstractType
{
    public function __construct(CallManager $callManager, TranslatorInterface $translator)
    {
        $this->callManager = $callManager;
        $this->translator = $translator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $fields = [];

        if (false == $options['update']) {
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
                        'choice_label' => function ($choice, $key, $value) use ($options) {
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
                case 'match_field':
                    $builder->add('match_field', TextType::class, [
                        'label' => 'match_field',
                        'required' => true,
                        'constraints' => [
                            new NotBlank(),
                        ],
                        'help' => 'help_form.enrich_policy.match_field',
                        'help_html' => true,
                    ]);
                    break;
                case 'enrich_fields':
                    /*$builder->add('enrich_fields', ChoiceType::class, [
                        'multiple' => true,
                        'choices' => [],
                        'choice_label' => function ($choice, $key, $value) use ($options) {
                            return $key;
                        },
                        'choice_translation_domain' => false,
                        'label' => 'enrich_fields',
                        'required' => true,
                        'constraints' => [
                            new NotBlank(),
                        ],
                        'attr' => [
                            'data-break-after' => 'yes',
                        ],
                        'help' => 'help_form.enrich_policy.enrich_fields',
                        'help_html' => true,
                    ]);*/
                    break;
                case 'query':
                    $builder->add('query', TextType::class, [
                        'label' => 'query',
                        'required' => false,
                        'help' => 'help_form.enrich_policy.query',
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
    }

    private function enrichFields($form, $indices, $selected)
    {
        $choices = [];

        if ($indices && 0 < count($indices)) {
            $callRequest = new CallRequestModel();
            $callRequest->setPath('/'.implode(',', $indices).'/_mapping');
            $callResponse = $this->callManager->call($callRequest);

            $results = $callResponse->getContent();

            foreach ($results as $result) {
                if (true == isset($result['mappings']) && true == isset($result['mappings']['properties'])) {
                    foreach ($result['mappings']['properties'] as $k => $property) {
                        $choices[$k] = $k;
                    }
                }
            }
        }

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
                'data-break-after' => 'yes',
            ],
            'help' => 'help_form.enrich_policy.enrich_fields',
            'help_html' => true,
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ElasticsearchEnrichPolicyModel::class,
            'indices' => [],
            'update' => false,
        ]);
    }

    public function getBlockPrefix()
    {
        return 'data';
    }
}
