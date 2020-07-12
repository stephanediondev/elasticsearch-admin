<?php

namespace App\Form;

use App\Manager\CallManager;
use App\Model\CallRequestModel;
use App\Security\User;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormError;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Contracts\Translation\TranslatorInterface;

class RegisterUserType extends AbstractType
{
    public function __construct(CallManager $callManager, TranslatorInterface $translator, string $secretRegister)
    {
        $this->callManager = $callManager;
        $this->translator = $translator;
        $this->secretRegister = $secretRegister;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $fields = [];

        $fields[] = 'secretRegister';
        $fields[] = 'email';
        $fields[] = 'passwordPlain';

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
                case 'passwordPlain':
                    $builder->add('passwordPlain', RepeatedType::class, [
                        'type' => PasswordType::class,
                        'required' => true,
                        'constraints' => [
                            new NotBlank(),
                        ],
                        'first_options'  => [
                            'label' => 'password',
                            'attr' => [
                                'autocomplete' => 'new-password',
                                'minlength' => 6,
                            ],
                        ],
                        'second_options' => [
                            'label' => 'password_confirm',
                            'attr' => [
                                'autocomplete' => 'new-password',
                                'minlength' => 6,
                            ],
                        ],
                    ]);
                    break;
            }
        }

        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) use ($options) {
            $form = $event->getForm();

            if ($form->has('secretRegister') && $form->get('secretRegister')->getData()) {
                if ($this->secretRegister !== $form->get('secretRegister')->getData()) {
                    $form->get('secretRegister')->addError(new FormError(
                        $this->translator->trans('secret_register_wrong')
                    ));
                }
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }

    public function getBlockPrefix()
    {
        return 'data';
    }
}
