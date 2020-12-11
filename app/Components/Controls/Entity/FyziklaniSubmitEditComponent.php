<?php

namespace FKSDB\Components\Controls\Entity;

use FKSDB\Model\Exceptions\BadTypeException;
use FKSDB\Model\Fyziklani\NotSetGameParametersException;
use FKSDB\Model\Fyziklani\Submit\ClosedSubmittingException;
use FKSDB\Model\Fyziklani\Submit\HandlerFactory;
use Fykosak\Utils\Logging\FlashMessageDump;
use Fykosak\Utils\Logging\MemoryLogger;
use FKSDB\Model\ORM\Models\Fyziklani\ModelFyziklaniSubmit;
use FKSDB\Model\ORM\Models\ModelEvent;
use Fykosak\Utils\Logging\Message;
use Nette\Application\AbortException;
use Nette\DI\Container;
use Nette\Forms\Controls\RadioList;
use Nette\Forms\Form;

/**
 * Class EditControl
 * @author Michal Červeňák <miso@fykos.cz>
 * @property ModelFyziklaniSubmit $model
 */
class FyziklaniSubmitEditComponent extends AbstractEntityFormComponent {
    private ModelEvent $event;
    private HandlerFactory $handlerFactory;

    public function __construct(Container $container, ModelEvent $event, ModelFyziklaniSubmit $model) {
        parent::__construct($container, $model);
        $this->event = $event;
    }

    final public function injectHandlerFactory(HandlerFactory $handlerFactory): void {
        $this->handlerFactory = $handlerFactory;
    }

    /**
     * @param Form $form
     * @return void
     * @throws NotSetGameParametersException
     */
    protected function configureForm(Form $form): void {
        $form->addComponent($this->createPointsField(), 'points');
    }

    /**
     * @return void
     * @throws BadTypeException
     */
    protected function setDefaults(): void {
        if (isset($this->model)) {
            $this->getForm()->setDefaults([
                'team_id' => $this->model->e_fyziklani_team_id,
                'points' => $this->model->points,
            ]);
        }
    }

    /**
     * @param Form $form
     * @throws AbortException
     */
    protected function handleFormSuccess(Form $form): void {
        $values = $form->getValues();
        try {
            $logger = new MemoryLogger();
            $handler = $this->handlerFactory->create($this->event);
            $handler->changePoints($logger, $this->model, $values['points']);
            FlashMessageDump::dump($logger, $this->getPresenter());
            $this->redirect('this');
        } catch (ClosedSubmittingException $exception) {
            $this->getPresenter()->flashMessage($exception->getMessage(), Message::LVL_ERROR);
            $this->redirect('this');
        }
    }

    /**
     * @return RadioList
     * TODO to table-reflection factory
     * @throws NotSetGameParametersException
     */
    private function createPointsField(): RadioList {
        $field = new RadioList(_('Number of points'));
        $items = [];
        foreach ($this->event->getFyziklaniGameSetup()->getAvailablePoints() as $points) {
            $items[$points] = $points;
        }
        $field->setItems($items);
        $field->setRequired();
        return $field;
    }
}
