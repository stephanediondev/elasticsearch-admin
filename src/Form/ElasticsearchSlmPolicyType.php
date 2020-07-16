<?php

namespace App\Form;

use App\Manager\ElasticsearchSlmPolicyManager;
use App\Model\CallRequestModel;
use App\Model\ElasticsearchSlmPolicyModel;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormError;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Contracts\Translation\TranslatorInterface;

class ElasticsearchSlmPolicyType extends AbstractType
{
    public function __construct(ElasticsearchSlmPolicyManager $elasticsearchSlmPolicyManager, TranslatorInterface $translator)
    {
        $this->elasticsearchSlmPolicyManager = $elasticsearchSlmPolicyManager;
        $this->translator = $translator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $fields = [];

        if ('create' == $options['context']) {
            $fields[] = 'name';
        }
        $fields[] = 'snapshot_name';
        $fields[] = 'repository';
        $fields[] = 'schedule';
        $fields[] = 'indices';
        $fields[] = 'expire_after';
        $fields[] = 'min_count';
        $fields[] = 'max_count';
        $fields[] = 'ignore_unavailable';
        $fields[] = 'partial';
        $fields[] = 'include_global_state';

        foreach ($fields as $field) {
            switch ($field) {
                case 'name':
                    $builder->add('name', TextType::class, [
                        'label' => 'name',
                        'required' => true,
                        'constraints' => [
                            new NotBlank(),
                        ],
                        'help' => 'help_form.slm_policy.name',
                        'help_html' => true,
                    ]);
                    break;
                case 'snapshot_name':
                    $builder->add('snapshot_name', TextType::class, [
                        'label' => 'snapshot_name',
                        'required' => true,
                        'constraints' => [
                            new NotBlank(),
                        ],
                        'help' => 'help_form.slm_policy.snapshot_name',
                        'help_html' => true,
                    ]);
                    break;
                case 'repository':
                    $builder->add('repository', ChoiceType::class, [
                        'placeholder' => '-',
                        'choices' => $options['repositories'],
                        'choice_label' => function ($choice, $key, $value) use ($options) {
                            return $options['repositories'][$key];
                        },
                        'choice_translation_domain' => false,
                        'label' => 'repository',
                        'required' => true,
                        'constraints' => [
                            new NotBlank(),
                        ],
                        'help' => 'help_form.slm_policy.repository',
                        'help_html' => true,
                    ]);
                    break;
                case 'schedule':
                    $builder->add('schedule', TextType::class, [
                        'label' => 'schedule',
                        'required' => true,
                        'constraints' => [
                            new NotBlank(),
                        ],
                        'help' => 'help_form.slm_policy.schedule',
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
                        'required' => false,
                        'attr' => [
                            'data-break-after' => 'yes',
                        ],
                        'help' => 'help_form.slm_policy.indices',
                        'help_html' => true,
                    ]);
                    break;
                case 'expire_after':
                    $builder->add('expire_after', TextType::class, [
                        'label' => 'expire_after',
                        'required' => false,
                        'help' => 'help_form.slm_policy.expire_after',
                        'help_html' => true,
                    ]);
                    break;
                case 'min_count':
                    $builder->add('min_count', IntegerType::class, [
                        'label' => 'min_count',
                        'required' => false,
                        'help' => 'help_form.slm_policy.min_count',
                        'help_html' => true,
                    ]);
                    break;
                case 'max_count':
                    $builder->add('max_count', IntegerType::class, [
                        'label' => 'max_count',
                        'required' => false,
                        'help' => 'help_form.slm_policy.max_count',
                        'help_html' => true,
                    ]);
                    break;
                case 'ignore_unavailable':
                    $builder->add('ignore_unavailable', CheckboxType::class, [
                        'label' => 'ignore_unavailable',
                        'required' => false,
                        'help' => 'help_form.slm_policy.ignore_unavailable',
                        'help_html' => true,
                    ]);
                    break;
                case 'partial':
                    $builder->add('partial', CheckboxType::class, [
                        'label' => 'partial',
                        'required' => false,
                        'help' => 'help_form.slm_policy.partial',
                        'help_html' => true,
                    ]);
                    break;
                case 'include_global_state':
                    $builder->add('include_global_state', CheckboxType::class, [
                        'label' => 'include_global_state',
                        'required' => false,
                        'help' => 'help_form.slm_policy.include_global_state',
                        'help_html' => true,
                    ]);
                    break;
            }
        }

        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) use ($options) {
            $form = $event->getForm();

            if ('create' == $options['context']) {
                if ($form->has('name') && $form->get('name')->getData()) {
                    $policy = $this->elasticsearchSlmPolicyManager->getByName($form->get('name')->getData());

                    if ($policy) {
                        $form->get('name')->addError(new FormError(
                            $this->translator->trans('name_already_used')
                        ));
                    }
                }
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ElasticsearchSlmPolicyModel::class,
            'repositories' => [],
            'indices' => [],
            'context' => 'create',
        ]);
    }

    public function getBlockPrefix()
    {
        return 'data';
    }
}
