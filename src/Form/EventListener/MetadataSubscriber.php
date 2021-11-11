<?php

namespace App\Form\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class MetadataSubscriber implements EventSubscriberInterface
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

        if ($form->has('metadata') && $form->get('metadata')->getData()) {
            $fieldOptions = $form->get('metadata')->getConfig()->getOptions();
            $fieldOptions['data'] = json_encode($form->get('metadata')->getData(), JSON_PRETTY_PRINT);
            $form->add('metadata', TextareaType::class, $fieldOptions);
        }
    }

    public function postSubmit(FormEvent $event): void
    {
        $form = $event->getForm();
        $data = $event->getData();

        if ($form->has('metadata') && $form->get('metadata')->getData()) {
            $data->setMetadata(json_decode($form->get('metadata')->getData(), true));
            $event->setData($data);
        }
    }
}
