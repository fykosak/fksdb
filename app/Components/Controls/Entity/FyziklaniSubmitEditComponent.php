<?php

namespace FKSDB\Components\Controls\Entity;

use FKSDB\Exceptions\BadTypeException;
use FKSDB\Fyziklani\NotSetGameParametersException;
use FKSDB\Fyziklani\Submit\ClosedSubmittingException;
use FKSDB\Fyziklani\Submit\HandlerFactory;
use FKSDB\Logging\FlashMessageDump;
use FKSDB\Logging\MemoryLogger;
use FKSDB\Modules\Core\BasePresenter;
use FKSDB\ORM\AbstractModelSingle;
use FKSDB\ORM\Models\Fyziklani\ModelFyziklaniSubmit;
use FKSDB\ORM\Models\ModelEvent;
use Nette\Application\AbortException;
use Nette\Forms\Form;
use Nette\DI\Container;
use Nette\Forms\Controls\RadioList;

/**
 * Class EditControl
 * @author Michal Červeňák <miso@fykos.cz>
 * @property ModelFyziklaniSubmit $model
 */
class FyziklaniSubmitEditComponent extends EditEntityFormComponent {

    private ModelEvent $event;
    private HandlerFactory $handlerFactory;

    public function __construct(Container $container, ModelEvent $event) {
        parent::__construct($container, false);
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
     * @param AbstractModelSingle|ModelFyziklaniSubmit|null $model
     * @return void
     * @throws BadTypeException
     */
    protected function setDefaults(?AbstractModelSingle $model): void {
        if (!is_null($model)) {
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
            $this->getPresenter()->flashMessage($exception->getMessage(), BasePresenter::FLASH_ERROR);
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
