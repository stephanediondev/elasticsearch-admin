<?php

namespace App\Form\Type;

use App\Manager\ElasticsearchIlmPolicyManager;
use App\Model\CallRequestModel;
use App\Model\ElasticsearchIlmPolicyModel;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormError;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Json;
use Symfony\Contracts\Translation\TranslatorInterface;

class ElasticsearchIlmPolicyType extends AbstractType
{
    protected ElasticsearchIlmPolicyManager $elasticsearchIlmPolicyManager;

    protected TranslatorInterface $translator;

    public function __construct(ElasticsearchIlmPolicyManager $elasticsearchIlmPolicyManager, TranslatorInterface $translator)
    {
        $this->elasticsearchIlmPolicyManager = $elasticsearchIlmPolicyManager;
        $this->translator = $translator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $fields = [];

        if ('create' == $options['context']) {
            $fields[] = 'name';
        }
        $fields[] = 'hot';
        $fields[] = 'warm';
        $fields[] = 'cold';
        $fields[] = 'delete';

        foreach ($fields as $field) {
            switch ($field) {
                case 'name':
                    $builder->add('name', TextType::class, [
                        'label' => 'name',
                        'required' => true,
                        'constraints' => [
                            new NotBlank(),
                        ],
                        'help' => 'help_form.ilm_policy.name',
                        'help_html' => true,
                    ]);
                    break;
                case 'hot':
                    $builder->add('hot', TextareaType::class, [
                        'label' => 'hot',
                        'required' => false,
                        'constraints' => [
                            new Json(),
                        ],
                        'help' => 'help_form.ilm_policy.hot',
                        'help_html' => true,
                    ]);
                    break;
                case 'warm':
                    $builder->add('warm', TextareaType::class, [
                        'label' => 'warm',
                        'required' => false,
                        'constraints' => [
                            new Json(),
                        ],
                        'attr' => [
                            'data-break-after' => 'yes',
                        ],
                        'help' => 'help_form.ilm_policy.warm',
                        'help_html' => true,
                    ]);
                    break;
                case 'cold':
                    $builder->add('cold', TextareaType::class, [
                        'label' => 'cold',
                        'required' => false,
                        'constraints' => [
                            new Json(),
                        ],
                        'help' => 'help_form.ilm_policy.cold',
                        'help_html' => true,
                    ]);
                    break;
                case 'delete':
                    $builder->add('delete', TextareaType::class, [
                        'label' => 'delete',
                        'required' => false,
                        'constraints' => [
                            new Json(),
                        ],
                        'help' => 'help_form.ilm_policy.delete',
                        'help_html' => true,
                    ]);
                    break;
            }
        }

        $builder->addEventListener(FormEvents::POST_SET_DATA, function (FormEvent $event) {
            $form = $event->getForm();

            if ($form->has('hot') && $form->get('hot')->getData()) {
                $fieldOptions = $form->get('hot')->getConfig()->getOptions();
                $fieldOptions['data'] = json_encode($form->get('hot')->getData(), JSON_PRETTY_PRINT);
                $form->add('hot', TextareaType::class, $fieldOptions);
            }

            if ($form->has('warm') && $form->get('warm')->getData()) {
                $fieldOptions = $form->get('warm')->getConfig()->getOptions();
                $fieldOptions['data'] = json_encode($form->get('warm')->getData(), JSON_PRETTY_PRINT);
                $form->add('warm', TextareaType::class, $fieldOptions);
            }

            if ($form->has('cold') && $form->get('cold')->getData()) {
                $fieldOptions = $form->get('cold')->getConfig()->getOptions();
                $fieldOptions['data'] = json_encode($form->get('cold')->getData(), JSON_PRETTY_PRINT);
                $form->add('cold', TextareaType::class, $fieldOptions);
            }

            if ($form->has('delete') && $form->get('delete')->getData()) {
                $fieldOptions = $form->get('delete')->getConfig()->getOptions();
                $fieldOptions['data'] = json_encode($form->get('delete')->getData(), JSON_PRETTY_PRINT);
                $form->add('delete', TextareaType::class, $fieldOptions);
            }
        });

        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) use ($options) {
            $form = $event->getForm();
            $data = $event->getData();

            if ('create' == $options['context']) {
                if ($form->has('name') && $form->get('name')->getData()) {
                    $policy = $this->elasticsearchIlmPolicyManager->getByName($form->get('name')->getData());

                    if ($policy) {
                        $form->get('name')->addError(new FormError(
                            $this->translator->trans('name_already_used')
                        ));
                    }
                }
            }

            if ($form->has('hot') && $form->get('hot')->getData()) {
                $data->setHot(json_decode($form->get('hot')->getData(), true));
                $event->setData($data);
            }

            if ($form->has('warm') && $form->get('warm')->getData()) {
                $data->setWarm(json_decode($form->get('warm')->getData(), true));
                $event->setData($data);
            }

            if ($form->has('cold') && $form->get('cold')->getData()) {
                $data->setCold(json_decode($form->get('cold')->getData(), true));
                $event->setData($data);
            }

            if ($form->has('delete') && $form->get('delete')->getData()) {
                $data->setDelete(json_decode($form->get('delete')->getData(), true));
                $event->setData($data);
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ElasticsearchIlmPolicyModel::class,
            'context' => 'create',
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'data';
    }
}
