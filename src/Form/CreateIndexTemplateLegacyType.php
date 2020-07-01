<?php

namespace App\Form;

use App\Manager\CallManager;
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

class CreateIndexTemplateLegacyType extends AbstractType
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
        $fields[] = 'index_patterns';
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
                case 'order':
                    $builder->add('order', IntegerType::class, [
                        'label' => 'order',
                        'required' => false,
                        'attr' => [
                            'data-break-after' => 'yes',
                        ],
                        'help' => 'help_form.index_template.order',
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
            }
        }

        if (false == $options['update']) {
            $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) use ($options) {
                $form = $event->getForm();

                if ($form->has('name')) {
                    if ($form->get('name')->getData()) {
                        $callRequest = new CallRequestModel();
                        $callRequest->setMethod('HEAD');
                        $callRequest->setPath('/_template/'.$form->get('name')->getData());
                        $callResponse = $this->callManager->call($callRequest);

                        if (Response::HTTP_OK == $callResponse->getCode()) {
                            $form->get('name')->addError(new FormError(
                                $this->translator->trans('name_already_used')
                            ));
                        }
                    }
                }
            });
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ElasticsearchIndexTemplateLegacyModel::class,
            'update' => false,
        ]);
    }

    public function getBlockPrefix()
    {
        return 'data';
    }
}
