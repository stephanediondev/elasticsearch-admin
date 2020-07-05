<?php

namespace App\Form;

use App\Manager\CallManager;
use App\Model\CallRequestModel;
use App\Model\ElasticsearchPipelineModel;
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

class CreatePipelineType extends AbstractType
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
        $fields[] = 'description';
        $fields[] = 'version';
        $fields[] = 'processors';
        $fields[] = 'on_failure';

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
                case 'processors':
                    $builder->add('processors', TextareaType::class, [
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
                case 'on_failure':
                    $builder->add('on_failure', TextareaType::class, [
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

        $builder->addEventListener(FormEvents::POST_SET_DATA, function (FormEvent $event) use ($options) {
            $form = $event->getForm();

            if ($form->has('processors') && $form->get('processors')->getData()) {
                $fieldOptions = $form->get('processors')->getConfig()->getOptions();
                $fieldOptions['data'] = json_encode($form->get('processors')->getData(), JSON_PRETTY_PRINT);
                $form->add('processors', TextareaType::class, $fieldOptions);
            }

            if ($form->has('on_failure') && $form->get('on_failure')->getData()) {
                $fieldOptions = $form->get('on_failure')->getConfig()->getOptions();
                $fieldOptions['data'] = json_encode($form->get('on_failure')->getData(), JSON_PRETTY_PRINT);
                $form->add('on_failure', TextareaType::class, $fieldOptions);
            }
        });

        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) use ($options) {
            $form = $event->getForm();

            if (false == $options['update']) {
                if ($form->has('name') && $form->get('name')->getData()) {
                    $callRequest = new CallRequestModel();
                    $callRequest->setMethod('GET');
                    $callRequest->setPath('/_ingest/pipeline/'.$form->get('name')->getData());
                    $callResponse = $this->callManager->call($callRequest);

                    if (Response::HTTP_OK == $callResponse->getCode()) {
                        $form->get('name')->addError(new FormError(
                            $this->translator->trans('name_already_used')
                        ));
                    }
                }
            }

            if ($form->has('processors') && $form->get('processors')->getData()) {
                $template = $event->getData();
                $template->setProcessors(json_decode($form->get('processors')->getData(), true));
                $event->setData($template);
            }

            if ($form->has('on_failure') && $form->get('on_failure')->getData()) {
                $template = $event->getData();
                $template->setOnFailure(json_decode($form->get('on_failure')->getData(), true));
                $event->setData($template);
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ElasticsearchPipelineModel::class,
            'update' => false,
        ]);
    }

    public function getBlockPrefix()
    {
        return 'data';
    }
}
