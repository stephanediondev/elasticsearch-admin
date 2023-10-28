<?php

declare(strict_types=1);

namespace App\Form\Type;

use App\Manager\AppRoleManager;
use App\Model\AppRoleModel;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Contracts\Translation\TranslatorInterface;

class AppRoleType extends AbstractType
{
    protected AppRoleManager $appRoleManager;

    protected TranslatorInterface $translator;

    public function __construct(AppRoleManager $appRoleManager, TranslatorInterface $translator)
    {
        $this->appRoleManager = $appRoleManager;
        $this->translator = $translator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $fields = [];

        if ('create' == $options['context']) {
            $fields[] = 'name';
        }

        foreach ($fields as $field) {
            switch ($field) {
                case 'name':
                    $builder->add('name', TextType::class, [
                        'label' => 'name',
                        'required' => true,
                        'constraints' => [
                            new NotBlank(),
                            new Regex([
                                'pattern' => '/^[A-Z_]+$/',
                                'htmlPattern' => '^[A-Z_]+$',
                            ]),
                        ],
                        'help' => 'help_form.app_role.name',
                        'help_html' => true,
                    ]);
                    break;
            }
        }

        $builder->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) use ($options) {
            $form = $event->getForm();
            $data = $event->getData();

            if ('create' == $options['context']) {
                if ($form->has('name') && $form->get('name')->getData()) {
                    if ('USER' == $form->get('name')->getData()) {
                        $form->get('name')->addError(new FormError(
                            $this->translator->trans('not_allowed')
                        ));
                    }

                    $role = $this->appRoleManager->getByName('ROLE_'.$form->get('name')->getData());

                    if ($role) {
                        $form->get('name')->addError(new FormError(
                            $this->translator->trans('name_already_used')
                        ));
                    }
                }
            }

            if ($form->has('name') && $form->get('name')->getData()) {
                $data->setName('ROLE_'.$form->get('name')->getData());
                $event->setData($data);
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => AppRoleModel::class,
            'context' => 'create',
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'data';
    }
}
