<?php

namespace App\Form\Type;

use App\Model\ElasticsearchClusterDiskThresholdsModel;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ElasticsearchClusterDiskThresholdsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $fields = [];

        $fields[] = 'enabled';
        $fields[] = 'low';
        $fields[] = 'high';
        if (true === isset($options['cluster_settings']['cluster.routing.allocation.disk.watermark.flood_stage'])) {
            $fields[] = 'flood_stage';
        }

        foreach ($fields as $field) {
            switch ($field) {
                case 'enabled':
                    $builder->add('enabled', CheckboxType::class, [
                        'label' => 'enabled',
                        'required' => false,
                    ]);
                    break;
                case 'low':
                    $builder->add('low', TextType::class, [
                        'label' => 'watermark_low',
                        'required' => false,
                        'help' => 'help_form.disk_thresholds.low',
                        'help_html' => true,
                    ]);
                    break;
                case 'high':
                    $builder->add('high', TextType::class, [
                        'label' => 'watermark_high',
                        'required' => false,
                        'help' => 'help_form.disk_thresholds.high',
                        'help_html' => true,
                    ]);
                    break;
                case 'flood_stage':
                    $builder->add('flood_stage', TextType::class, [
                        'label' => 'watermark_flood_stage',
                        'required' => false,
                        'help' => 'help_form.disk_thresholds.flood_stage',
                        'help_html' => true,
                    ]);
                    break;
            }
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ElasticsearchClusterDiskThresholdsModel::class,
            'cluster_settings' => [],
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'data';
    }
}
