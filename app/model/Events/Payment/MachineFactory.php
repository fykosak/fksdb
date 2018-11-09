<?php

namespace Events\Payment;

use Events\Payment\EventFactories\EventTransitionFactory;
use Events\Payment\EventFactories\Fyziklani13Payment;
use FKSDB\ORM\ModelEvent;
use FKSDB\ORM\ModelEventPayment;
use Mail\MailTemplateFactory;
use Nette\Diagnostics\Debugger;
use Nette\Mail\IMailer;
use Nette\Mail\Message;
use Nette\NotImplementedException;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class MachineFactory {
    private $mailer;
    private $mailTemplateFactory;

    public function __construct(IMailer $mailer, MailTemplateFactory $mailTemplateFactory) {
        $this->mailer = $mailer;
        $this->mailTemplateFactory = $mailTemplateFactory;
    }

    public function createTransition(string $fromState = null, string $toState, string $label) {
        $transition = new Transition($fromState, $toState, $label);

        return $transition;
    }

    private function findMachineFactory(ModelEvent $event): EventTransitionFactory {
        if (($event->event_type_id === 1) && ($event->event_year === 13)) {
            return new Fyziklani13Payment($this);
        }
        throw new NotImplementedException('Not implemented');
    }

    public function createMailCallback($templateFile, string $address, $options) {
        $template = $this->mailTemplateFactory->createFromFile($templateFile);
        $message = new Message();

        $message->setSubject($options->subject);
        $message->setFrom($options->from);
        $message->addBcc($options->bcc);
        $message->addTo($address);
        //  $message->addAttachment()

        return function (ModelEventPayment $model) use ($message, $template) {

            $template->model = $model;

            $message->setHtmlBody($template);

            $this->mailer->send($message);
            Debugger::barDump($message);
        };
    }

    public function setUpMachine(ModelEvent $event, $state = null): Machine {

        $factory = $this->findMachineFactory($event);
        $machine = $factory->createMachine($state);
        $factory->createTransitions($machine);
        // $this->machine->setInitState(self::STATE_WAITING);
        return $machine;
    }
}

