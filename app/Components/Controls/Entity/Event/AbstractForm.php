<?php

namespace FKSDB\Components\Controls\Entity\Event;

use FKSDB\Components\Controls\Entity\AbstractEntityFormControl;
use FKSDB\Components\Forms\Factories\EventFactory;
use FKSDB\Exceptions\ModelException;
use FKSDB\Logging\ILogger;
use FKSDB\ORM\AbstractModelSingle;
use FKSDB\ORM\Models\ModelContest;
use FKSDB\ORM\Models\ModelEvent;
use FKSDB\ORM\Services\ServiceAuthToken;
use Nette\Application\UI\Form;
use Nette\DI\Container;
use Tracy\Debugger;

/**
 * Class AbstractForm
 * @author Michal Červeňák <miso@fykos.cz>
 */
abstract class AbstractForm extends AbstractEntityFormControl {
    const CONT_EVENT = 'event';
    /**
     * @var ModelContest
     */
    protected $contest;
    /**
     * @var EventFactory
     */
    protected $eventFactory;
    /**
     * @var ServiceAuthToken
     */
    protected $serviceAuthToken;

    /**
     * AbstractForm constructor.
     * @param ModelContest $contest
     * @param Container $container
     */
    public function __construct(ModelContest $contest, Container $container) {
        parent::__construct($container);
        $this->contest = $contest;
    }

    /**
     * @param EventFactory $eventFactory
     * @param ServiceAuthToken $serviceAuthToken
     * @return void
     */
    public function injectPrimary(EventFactory $eventFactory, ServiceAuthToken $serviceAuthToken) {
        $this->serviceAuthToken = $serviceAuthToken;
        $this->eventFactory = $eventFactory;
    }

    /**
     * @param Form $form
     * @return void
     * @throws \Exception
     */
    protected function configureForm(Form $form) {
        $eventContainer = $this->eventFactory->createEvent($this->contest);
        $form->addComponent($eventContainer, self::CONT_EVENT);
    }

    /**
     * @param ModelEvent|AbstractModelSingle $event
     */
    protected function updateTokens(ModelEvent $event) {
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
