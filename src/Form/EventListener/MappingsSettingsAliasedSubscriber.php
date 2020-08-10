<?php

namespace App\Form\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class MappingsSettingsAliasedSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        // Tells the dispatcher that you want to listen on the form.pre_set_data
        // event and that the preSetData method should be called.
        return [FormEvents::POST_SET_DATA => 'postSetData'];
    }

    public function postSetData(FormEvent $event)
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
}
