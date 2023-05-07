<?php

declare(strict_types=1);

namespace App\Form\Type;

use App\Manager\AppUserManager;
use App\Model\AppUserModel;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Contracts\Translation\TranslatorInterface;

class AppUserType extends AbstractType
{
    protected AppUserManager $appUserManager;

    protected TranslatorInterface $translator;

    protected string $secretRegister;

    public function __construct(AppUserManager $appUserManager, TranslatorInterface $translator, string $secretRegister)
    {
        $this->appUserManager = $appUserManager;
        $this->translator = $translator;
        $this->secretRegister = $secretRegister;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $fields = [];

        $passwordRequired = true;
        $passwordConstraints = [
            new NotBlank(),
        ];

        if ('register' == $options['context']) {
            $fields[] = 'secretRegister';
        }

        $fields[] = 'email';

        if ('update' == $options['context'] || 'profile' == $options['context']) {
            $fields[] = 'change_password';

            $passwordRequired = false;
            $passwordConstraints = [];
        }

        $fields[] = 'passwordPlain';

        if ('register' != $options['context'] && 'profile' != $options['context'] && false === $options['current_user_admin']) {
            $fields[] = 'roles';
        }

        foreach ($fields as $field) {
            switch ($field) {
                case 'secretRegister':
                    $builder->add('secretRegister', PasswordType::class, [
                        'label' => 'secret_register',
                        'required' => true,
                        'constraints' => [
                            new NotBlank(),
                        ],
                    ]);
                    break;
                case 'email':
                    $builder->add('email', EmailType::class, [
                        'label' => 'email',
                        'required' => true,
                        'constraints' => [
                            new NotBlank(),
                        ],
                        'attr' => [
                            'autocomplete' => 'nope',
                        ],
                    ]);
                    break;
                case 'change_password':
                    $builder->add('change_password', CheckboxType::class, [
                        'label' => 'change_password',
                        'required' => false,
                    ]);
                    break;
                case 'passwordPlain':
                    $builder->add('passwordPlain', RepeatedType::class, [
                        'type' => PasswordType::class,
                        'required' => $passwordRequired,
                        'constraints' => $passwordConstraints,
                        'first_options'  => [
                            'label' => 'password',
                            'attr' => [
                                'autocomplete' => 'new-password',
                            ],
                        ],
                        'second_options' => [
                            'label' => 'password_confirm',
                            'attr' => [
                                'autocomplete' => 'new-password',
                            ],
                        ],
                    ]);
                    break;
                case 'roles':
                    $builder->add('roles', ChoiceType::class, [
                        'multiple' => true,
                        'choices' => $options['roles'],
                        'choice_label' => function ($choice, $key, $value) use ($options) {
                            return $options['roles'][$key];
                        },
                        'choice_translation_domain' => false,
                        'label' => 'roles',
                        'required' => false,
                        'attr' => [
                            'data-break-after' => 'yes',
                        ],
                    ]);
                    break;
            }
        }

        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) use ($options) {
            $form = $event->getForm();

            if ('register' == $options['context']) {
                if ($form->has('secretRegister') && $form->get('secretRegister')->getData()) {
                    if ($this->secretRegister !== $form->get('secretRegister')->getData()) {
                        $form->get('secretRegister')->addError(new FormError(
                            $this->translator->trans('secret_register_wrong')
                        ));
                    }
                }
            }

            if ('create' == $options['context']) {
                if ($form->has('email') && $form->get('email')->getData()) {
                    $user = $this->appUserManager->getByEmail($form->get('email')->getData());

                    if ($user) {
                        $form->get('email')->addError(new FormError(
                            $this->translator->trans('email_already_used')
                        ));
                    }
                }
            }

            if ('update' == $options['context'] || 'profile' == $options['context']) {
                if ($form->has('email') && $form->get('email')->getData() && $options['old_email'] != $form->get('email')->getData()) {
                    $user = $this->appUserManager->getByEmail($form->get('email')->getData());

                    if ($user) {
                        $form->get('email')->addError(new FormError(
                            $this->translator->trans('email_already_used')
                        ));
                    }
                }
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => AppUserModel::class,
            'roles' => [],
            'context' => 'create',
            'old_email' => false,
            'current_user_admin' => false,
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'data';
    }
}
