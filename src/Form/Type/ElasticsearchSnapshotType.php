<?php
declare(strict_types=1);

namespace App\Form\Type;

use App\Manager\ElasticsearchSnapshotManager;
use App\Model\ElasticsearchSnapshotModel;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Contracts\Translation\TranslatorInterface;

class ElasticsearchSnapshotType extends AbstractType
{
    protected ElasticsearchSnapshotManager $elasticsearchSnapshotManager;

    protected TranslatorInterface $translator;

    public function __construct(ElasticsearchSnapshotManager $elasticsearchSnapshotManager, TranslatorInterface $translator)
    {
        $this->elasticsearchSnapshotManager = $elasticsearchSnapshotManager;
        $this->translator = $translator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $fields = [];

        $fields[] = 'repository';
        $fields[] = 'name';
        $fields[] = 'indices';
        $fields[] = 'ignore_unavailable';
        $fields[] = 'partial';
        $fields[] = 'include_global_state';

        foreach ($fields as $field) {
            switch ($field) {
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
                        'help' => 'help_form.snapshot.repository',
                        'help_html' => true,
                    ]);
                    break;
                case 'name':
                    $builder->add('name', TextType::class, [
                        'label' => 'name',
                        'required' => true,
                        'constraints' => [
                            new NotBlank(),
                        ],
                        'help' => 'help_form.snapshot.name',
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
                        'help' => 'help_form.snapshot.indices',
                        'help_html' => true,
                    ]);
                    break;
                case 'ignore_unavailable':
                    $builder->add('ignore_unavailable', CheckboxType::class, [
                        'label' => 'ignore_unavailable',
                        'required' => false,
                        'help' => 'help_form.snapshot.ignore_unavailable',
                        'help_html' => true,
                    ]);
                    break;
                case 'partial':
                    $builder->add('partial', CheckboxType::class, [
                        'label' => 'partial',
                        'required' => false,
                        'help' => 'help_form.snapshot.partial',
                        'help_html' => true,
                    ]);
                    break;
                case 'include_global_state':
                    $builder->add('include_global_state', CheckboxType::class, [
                        'label' => 'include_global_state',
                        'required' => false,
                        'help' => 'help_form.snapshot.include_global_state',
                        'help_html' => true,
                    ]);
                    break;
            }
        }

        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) use ($options) {
            $form = $event->getForm();

            if ('create' == $options['context']) {
                if ($form->has('repository') && $form->has('name')) {
                    if ($form->get('repository')->getData() && $form->get('name')->getData()) {
                        $snapshot = $this->elasticsearchSnapshotManager->getByNameAndRepository($form->get('name')->getData(), $form->get('repository')->getData());

                        if ($snapshot) {
                            $form->get('name')->addError(new FormError(
                                $this->translator->trans('name_already_used')
                            ));
                        }
                    }
                }
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ElasticsearchSnapshotModel::class,
            'repositories' => [],
            'indices' => [],
            'context' => 'create',
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'data';
    }
}
