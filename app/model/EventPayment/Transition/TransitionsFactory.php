<?php

namespace FKSDB\EventPayment\Transition;

use FKSDB\EventPayment\PriceCalculator\PriceCalculator;
use FKSDB\EventPayment\PriceCalculator\PriceCalculatorFactory;
use FKSDB\EventPayment\SymbolGenerator\AbstractSymbolGenerator;
use FKSDB\EventPayment\SymbolGenerator\SymbolGeneratorFactory;
use FKSDB\EventPayment\Transition\Transitions\Fyziklani13Payment;
use FKSDB\ORM\ModelEvent;
use FKSDB\ORM\ModelEventPayment;
use Mail\MailTemplateFactory;
use Nette\Mail\IMailer;
use Nette\Mail\Message;
use Nette\NotImplementedException;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class TransitionsFactory {
    private $mailer;
    private $mailTemplateFactory;

    /**
     * @var PriceCalculatorFactory
     */
    private $priceCalculatorFactory;

    /**
     * @var SymbolGeneratorFactory
     */
    private $symbolGeneratorFactory;


    public function __construct(IMailer $mailer, MailTemplateFactory $mailTemplateFactory) {
        $this->mailer = $mailer;
        $this->mailTemplateFactory = $mailTemplateFactory;
    }

    public function createTransition(string $fromState = null, string $toState, string $label) {
        $transition = new Transition($fromState, $toState, $label);

        return $transition;
    }

    private function createEventTransitions(ModelEvent $event): AbstractEventTransitions {
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
        };
    }

    private function getPriceCalculator(ModelEvent $event, $currency): PriceCalculator {
        return $this->priceCalculatorFactory->createCalculator($event, $currency);
    }

    private function getSymbolGenerator(ModelEvent $event): AbstractSymbolGenerator {
        return $this->symbolGeneratorFactory->createGenerator($event);
    }

    public function setUpMachine(ModelEvent $event): Machine {
        $factory = $this->createEventTransitions($event);
        $machine = $factory->createMachine();
        $factory->createTransitions($machine);

        return $machine;
    }
}

