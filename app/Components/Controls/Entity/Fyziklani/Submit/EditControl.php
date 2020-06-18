<?php

namespace FKSDB\Components\Controls\Entity\Fyziklani\Submit;

use FKSDB\Components\Controls\Entity\AbstractEntityFormControl;
use FKSDB\Components\Controls\Entity\IEditEntityForm;
use FKSDB\Exceptions\BadTypeException;
use FKSDB\Fyziklani\ClosedSubmittingException;
use FKSDB\Fyziklani\NotSetGameParametersException;
use FKSDB\ORM\AbstractModelSingle;
use FKSDB\ORM\Models\Fyziklani\ModelFyziklaniSubmit;
use FKSDB\ORM\Models\ModelEvent;
use FKSDB\ORM\Services\Fyziklani\ServiceFyziklaniSubmit;
use Nette\Application\AbortException;
use Nette\Application\UI\Form;
use Nette\DI\Container;
use Nette\Forms\Controls\RadioList;

/**
 * Class EditControl
 * @author Michal Červeňák <miso@fykos.cz>
 */
class EditControl extends AbstractEntityFormControl implements IEditEntityForm {
    /**
     * @var ServiceFyziklaniSubmit
     */
    private $serviceFyziklaniSubmit;
    /**
     * @var ModelFyziklaniSubmit
     */
    private $submit;
    /**
     * @var ModelEvent
     */
    private $event;

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
     * @param ServiceFyziklaniSubmit $serviceFyziklaniSubmit
     * @return void
     */
    public function injectServiceFyziklaniSubmit(ServiceFyziklaniSubmit $serviceFyziklaniSubmit) {
        $this->serviceFyziklaniSubmit = $serviceFyziklaniSubmit;
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
        $field = new RadioList(_('Počet bodů'));
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
            $msg = $this->serviceFyziklaniSubmit->changePoints($this->submit, $values->points, $this->getPresenter()->getUser());
            $this->getPresenter()->flashMessage($msg->getMessage(), $msg->getLevel());
            $this->redirect('this');
        } catch (ClosedSubmittingException $exception) {
            $this->getPresenter()->flashMessage($exception->getMessage(), \FKSDB\CoreModule\BasePresenter::FLASH_ERROR);
            $this->redirect('this');
        }
    }
}
