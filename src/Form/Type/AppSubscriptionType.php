<?php

namespace App\Form\Type;

use App\Manager\AppSubscriptionManager;
use App\Model\CallRequestModel;
use App\Model\AppNotificationModel;
use App\Model\AppSubscriptionModel;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormError;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Contracts\Translation\TranslatorInterface;

class AppSubscriptionType extends AbstractType
{
    protected $appSubscriptionManager;

    protected $translator;

    public function __construct(AppSubscriptionManager $appSubscriptionManager, TranslatorInterface $translator)
    {
        $this->appSubscriptionManager = $appSubscriptionManager;
        $this->translator = $translator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $fields = [];

        if ('create' == $options['context']) {
            if (AppSubscriptionModel::TYPE_EMAIL == $options['type']) {
                $fields[] = 'email';
            } else {
                $fields[] = 'endpoint';

                if (AppSubscriptionModel::TYPE_PUSH == $options['type']) {
                    $fields[] = 'public_key';
                    $fields[] = 'authentication_secret';
                    $fields[] = 'content_encoding';
                }
            }
        }

        $fields[] = 'notifications';

        foreach ($fields as $field) {
            switch ($field) {
                case 'email':
                    $builder->add('endpoint', EmailType::class, [
                        'label' => 'email',
                        'required' => true,
                        'constraints' => [
                            new NotBlank(),
                        ],
                    ]);
                    break;
                case 'endpoint':
                    $builder->add('endpoint', TextType::class, [
                        'label' => 'endpoint',
                        'required' => true,
                        'constraints' => [
                            new NotBlank(),
                        ],
                    ]);
                    break;
                case 'public_key':
                    $builder->add('public_key', TextType::class, [
                        'label' => 'public_key',
                        'required' => true,
                        'constraints' => [
                            new NotBlank(),
                        ],
                        'attr' => [
                            'autocomplete' => 'nope',
                        ],
                    ]);
                    break;
                case 'authentication_secret':
                    $builder->add('authentication_secret', PasswordType::class, [
                        'label' => 'authentication_secret',
                        'required' => true,
                        'constraints' => [
                            new NotBlank(),
                        ],
                        'attr' => [
                            'autocomplete' => 'new-password',
                        ],
                    ]);
                    break;
                case 'content_encoding':
                    $builder->add('content_encoding', TextType::class, [
                        'label' => 'content_encoding',
                        'required' => true,
                        'constraints' => [
                            new NotBlank(),
                        ],
                    ]);
                    break;
                case 'notifications':
                    $builder->add('notifications', ChoiceType::class, [
                        'multiple' => true,
                        'choices' => AppNotificationModel::getTypes(),
                        'choice_label' => function ($choice, $key, $value) {
                            return $value;
                        },
                        'choice_translation_domain' => false,
                        'label' => 'notifications',
                        'required' => false,
                    ]);
                    break;
            }
        }

        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) use ($options) {
            $form = $event->getForm();

            if ($form->has('endpoint') && $form->get('endpoint')->getData()) {
                $subscription = $this->appSubscriptionManager->getByEndpoint($form->get('endpoint')->getData());

                if ($subscription) {
                    if (AppSubscriptionModel::TYPE_EMAIL == $options['type']) {
                        $form->get('endpoint')->addError(new FormError(
                            $this->translator->trans('email_already_used')
                        ));
                    } else {
                        $form->get('endpoint')->addError(new FormError(
                            $this->translator->trans('endpoint_already_used')
                        ));
                    }
                }
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => AppSubscriptionModel::class,
            'type' => false,
            'context' => 'create',
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'data';
    }
}
