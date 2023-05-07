<?php
declare(strict_types=1);

namespace App\Form\Type;

use App\Model\CallRequestModel;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Json;
use Symfony\Component\Validator\Constraints\NotBlank;

class ElasticsearchConsoleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $fields = [];

        $fields[] = 'method';
        $fields[] = 'path';
        $fields[] = 'body';

        foreach ($fields as $field) {
            switch ($field) {
                case 'method':
                    $builder->add('method', ChoiceType::class, [
                        'choices' => $options['methods'],
                        'choice_label' => function ($choice, $key, $value) {
                            return $value;
                        },
                        'choice_translation_domain' => false,
                        'label' => 'method',
                        'required' => true,
                        'constraints' => [
                            new NotBlank(),
                        ],
                    ]);
                    break;
                case 'path':
                    $builder->add('path', TextType::class, [
                        'label' => 'path',
                        'required' => false,
                        'attr' => [
                            'data-break-after' => 'yes',
                        ],
                    ]);
                    break;
                case 'body':
                    $builder->add('body', TextareaType::class, [
                        'label' => 'body',
                        'required' => false,
                        'constraints' => [
                            new Json(),
                        ],
                    ]);
                    break;
            }
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => CallRequestModel::class,
            'methods' => [],
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'data';
    }
}
