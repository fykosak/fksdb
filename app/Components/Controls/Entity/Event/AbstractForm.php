<?php

namespace FKSDB\Components\Controls\Entity\Event;

use FKSDB\Components\Controls\FormControl\FormControl;
use FKSDB\Components\Forms\Factories\EventFactory;
use FKSDB\Logging\ILogger;
use FKSDB\ORM\AbstractModelSingle;
use FKSDB\ORM\Models\ModelContest;
use FKSDB\ORM\Models\ModelEvent;
use FKSDB\ORM\Services\ServiceAuthToken;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Form;
use Nette\DI\Container;
use Tracy\Debugger;

/**
 * Class AbstractForm
 * @package FKSDB\Components\Controls\Entity\Event
 */
abstract class AbstractForm extends FormControl {
    const CONT_EVENT = 'event';
    /**
     * @var Container
     */
    protected $container;

    /**
     * EditControl constructor.
     * @param Container $container
     * @throws \Exception
     */
    public function __construct(Container $container) {
        parent::__construct();
        $this->container = $container;
    }

    /**
     * @param ModelContest $contest
     * @return Form
     * @throws BadRequestException
     * @throws \Exception
     */
    protected function createBaseForm(ModelContest $contest) {
        $form = $this->getForm();
        /** @var EventFactory $eventFactory */
        $eventFactory = $this->container->getByType(EventFactory::class);
        $eventContainer = $eventFactory->createEvent($contest);
        $form->addComponent($eventContainer, self::CONT_EVENT);
        return $form;
    }

    /**
     * @param ModelEvent|AbstractModelSingle $event
     */
    protected function updateTokens(ModelEvent $event) {
        /** @var ServiceAuthToken $serviceAuthToken */
        $serviceAuthToken = $this->container->getByType(ServiceAuthToken::class);
        $connection = $serviceAuthToken->getConnection();
        try {
            $connection->beginTransaction();
            // update also 'until' of authTokens in case that registration end has changed
            $tokenData = ['until' => $event->registration_end ?: $event->end];
            foreach ($serviceAuthToken->findTokensByEventId($event->event_id) as $token) {
                $serviceAuthToken->updateModel2($token, $tokenData);
            }
            $connection->commit();
        } catch (\FKSDB\Exceptions\ModelException $exception) {
            $connection->rollBack();
            Debugger::log($exception, Debugger::ERROR);
            $this->flashMessage(_('Chyba přidání akce.'), ILogger::ERROR);
        }
    }
}
