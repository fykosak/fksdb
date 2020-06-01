<?php

namespace FKSDB\Components\Controls\Entity\Event;

use FKSDB\Components\Controls\FormControl\FormControl;
use FKSDB\Components\Forms\Factories\EventFactory;
use FKSDB\Exceptions\ModelException;
use FKSDB\Logging\ILogger;
use FKSDB\ORM\AbstractModelSingle;
use FKSDB\ORM\Models\ModelContest;
use FKSDB\ORM\Models\ModelEvent;
use FKSDB\ORM\Services\ServiceAuthToken;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Form;
use Tracy\Debugger;

/**
 * Class AbstractForm
 * @author Michal Červeňák <miso@fykos.cz>
 */
abstract class AbstractForm extends FormControl {
    protected const CONT_EVENT = 'event';

    private EventFactory $eventFactory;

    private ServiceAuthToken $serviceAuthToken;

    public function injectPrimary(EventFactory $eventFactory, ServiceAuthToken $serviceAuthToken): void {
        $this->serviceAuthToken = $serviceAuthToken;
        $this->eventFactory = $eventFactory;
    }

    /**
     * @param ModelContest $contest
     * @return Form
     * @throws BadRequestException
     * @throws \Exception
     */
    protected function createBaseForm(ModelContest $contest): Form {
        $form = $this->getForm();
        $eventContainer = $this->eventFactory->createEvent($contest);
        $form->addComponent($eventContainer, self::CONT_EVENT);
        return $form;
    }

    protected function updateTokens(ModelEvent $event): void {
        $connection = $this->serviceAuthToken->getConnection();
        try {
            $connection->beginTransaction();
            // update also 'until' of authTokens in case that registration end has changed
            $tokenData = ['until' => $event->registration_end ?: $event->end];
            foreach ($this->serviceAuthToken->findTokensByEventId($event->event_id) as $token) {
                $this->serviceAuthToken->updateModel2($token, $tokenData);
            }
            $connection->commit();
        } catch (ModelException $exception) {
            $connection->rollBack();
            Debugger::log($exception, Debugger::ERROR);
            $this->flashMessage(_('Chyba přidání akce.'), ILogger::ERROR);
        }
    }
}
