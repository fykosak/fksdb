<?php

namespace FKSDB\Components\Controls\Entity\Fyziklani\Submit;

use FKSDB\Components\Controls\Entity\AbstractEntityFormComponent;
use FKSDB\Components\Controls\Entity\IEditEntityForm;
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
 */
class EditComponent extends AbstractEntityFormComponent implements IEditEntityForm {

    /** @var ModelFyziklaniSubmit */
    private $submit;

    /** @var ModelEvent */
    private $event;

    /** @var HandlerFactory */
    private $handlerFactory;

    /**
     * EditControl constructor.
     * @param Container $container
     * @param ModelEvent $event
     */
    public function __construct(Container $container, ModelEvent $event) {
        parent::__construct($container, false);
        $this->event = $event;
    }

    /**
     * @param Form $form
     * @return void
     * @throws NotSetGameParametersException
     */
    protected function configureForm(Form $form) {
        $form->addComponent($this->createPointsField(), 'points');
    }

    /**
     * @param HandlerFactory $handlerFactory
     * @return void
     */
    public function injectHandlerFactory(HandlerFactory $handlerFactory) {
        $this->handlerFactory = $handlerFactory;
    }

    /**
     * @param AbstractModelSingle $submit
     * @return void
     * @throws BadTypeException
     */
    public function setModel(AbstractModelSingle $submit) {
        $this->submit = $submit;
        $this->getForm()->setDefaults([
            'team_id' => $this->submit->e_fyziklani_team_id,
            'points' => $this->submit->points,
        ]);

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

    /**
     * @param Form $form
     * @throws AbortException
     */
    protected function handleFormSuccess(Form $form) {
        $values = $form->getValues();
        try {
            $logger = new MemoryLogger();
            $handler = $this->handlerFactory->create($this->event);
            $handler->changePoints($logger, $this->submit, $values['points']);
            FlashMessageDump::dump($logger, $this->getPresenter());
            $this->redirect('this');
        } catch (ClosedSubmittingException $exception) {
            $this->getPresenter()->flashMessage($exception->getMessage(), BasePresenter::FLASH_ERROR);
            $this->redirect('this');
        }
    }
}
