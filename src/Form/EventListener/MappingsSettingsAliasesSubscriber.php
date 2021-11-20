<?php
declare(strict_types=1);

namespace App\Form\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class MappingsSettingsAliasesSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            FormEvents::POST_SET_DATA => 'postSetData',
            FormEvents::POST_SUBMIT => 'postSubmit',
        ];
    }

    public function postSetData(FormEvent $event): void
    {
        $form = $event->getForm();

        if ($form->has('mappings') && $form->get('mappings')->getData()) {
            $fieldOptions = $form->get('mappings')->getConfig()->getOptions();
            $fieldOptions['data'] = json_encode($form->get('mappings')->getData(), JSON_PRETTY_PRINT);
            $form->add('mappings', TextareaType::class, $fieldOptions);
        }

        if ($form->has('settings') && $form->get('settings')->getData()) {
            $fieldOptions = $form->get('settings')->getConfig()->getOptions();
            $fieldOptions['data'] = json_encode($form->get('settings')->getData(), JSON_PRETTY_PRINT);
            $form->add('settings', TextareaType::class, $fieldOptions);
        }

        if ($form->has('aliases') && $form->get('aliases')->getData()) {
            $fieldOptions = $form->get('aliases')->getConfig()->getOptions();
            $fieldOptions['data'] = json_encode($form->get('aliases')->getData(), JSON_PRETTY_PRINT);
            $form->add('aliases', TextareaType::class, $fieldOptions);
        }
    }

    public function postSubmit(FormEvent $event): void
    {
        $form = $event->getForm();
        $data = $event->getData();

        if ($form->has('mappings') && $form->get('mappings')->getData()) {
            $data->setMappings(json_decode($form->get('mappings')->getData(), true));
            $event->setData($data);
        }

        if ($form->has('settings') && $form->get('settings')->getData()) {
            $data->setSettings(json_decode($form->get('settings')->getData(), true));
            $event->setData($data);
        }

        if ($form->has('aliases') && $form->get('aliases')->getData()) {
            $data->setAliases(json_decode($form->get('aliases')->getData(), true));
            $event->setData($data);
        }
    }
}
