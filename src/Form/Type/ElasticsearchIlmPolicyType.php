<?php
declare(strict_types=1);

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
        $fields[] = 'hot_json';
        $fields[] = 'warm_json';
        $fields[] = 'cold_json';
        $fields[] = 'delete_json';

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
                case 'hot_json':
                    $builder->add('hot_json', TextareaType::class, [
                        'label' => 'hot',
                        'required' => false,
                        'constraints' => [
                            new Json(),
                        ],
                        'help' => 'help_form.ilm_policy.hot',
                        'help_html' => true,
                    ]);
                    break;
                case 'warm_json':
                    $builder->add('warm_json', TextareaType::class, [
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
                case 'cold_json':
                    $builder->add('cold_json', TextareaType::class, [
                        'label' => 'cold',
                        'required' => false,
                        'constraints' => [
                            new Json(),
                        ],
                        'help' => 'help_form.ilm_policy.cold',
                        'help_html' => true,
                    ]);
                    break;
                case 'delete_json':
                    $builder->add('delete_json', TextareaType::class, [
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
