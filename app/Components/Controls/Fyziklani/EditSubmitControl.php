<?php

namespace FKSDB\Components\Controls\Fyziklani;

use FKSDB\Components\Controls\FormControl\FormControl;
use FKSDB\model\Fyziklani\ClosedSubmittingException;
use FKSDB\ORM\Models\Fyziklani\ModelFyziklaniSubmit;
use FKSDB\ORM\Models\ModelEvent;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Control;
use Nette\Forms\Controls\RadioList;
use Nette\Forms\Form;
use Nette\Localization\ITranslator;
use Nette\Templating\FileTemplate;

/**
 * Class EditSubmitControl
 * @package FKSDB\Components\Controls\Fyziklani
 * @property FileTemplate $template
 */
class EditSubmitControl extends Control {
    /**
     * @var \FKSDB\ORM\Services\Fyziklani\ServiceFyziklaniSubmit
     */
    private $serviceFyziklaniSubmit;
    /**
     * @var ModelFyziklaniSubmit
     */
    private $submit;
    /**
     * @var \FKSDB\ORM\Models\ModelEvent
     */
    private $event;
    /**
     * @var ITranslator
     */
    private $translator;

    /**
     * EditSubmitControl constructor.
     * @param \FKSDB\ORM\Models\ModelEvent $event
     * @param \FKSDB\ORM\Services\Fyziklani\ServiceFyziklaniSubmit $serviceFyziklaniSubmit
     * @param ITranslator $translator
     */
    public function __construct(ModelEvent $event, \FKSDB\ORM\Services\Fyziklani\ServiceFyziklaniSubmit $serviceFyziklaniSubmit, ITranslator $translator) {
        parent::__construct();
        $this->serviceFyziklaniSubmit = $serviceFyziklaniSubmit;
        $this->translator = $translator;
        $this->event = $event;
    }

    /**
     * @return Form
     * @throws BadRequestException
     */
    public function getForm(): Form {
        $control = $this->getComponent('form');
        if ($control instanceof FormControl) {
            return $control->getForm();
        }
        throw new BadRequestException('Expected FormControl got ' . \get_class($control));
    }

    /**
     * @param int $id
     * @throws BadRequestException
     * @throws ClosedSubmittingException
     */
    public function setSubmit(int $id) {
        $row = $this->serviceFyziklaniSubmit->findByPrimary($id);

        if (!$this->submit) {
            throw new BadRequestException(_('Neexistující submit.'), 404);
        }
        $this->submit = ModelFyziklaniSubmit::createFromTableRow($row);

        $team = $this->submit->getTeam();
        if (!$team->hasOpenSubmitting()) {
            throw new ClosedSubmittingException($team);
        }
        /**
         * @var FormControl $control
         */
        $control = $this->getComponent('form');
        $control->getForm()->setDefaults([
            'team_id' => $this->submit->e_fyziklani_team_id,
            'points' => $this->submit->points,
        ]);
    }

    /**
     * @return RadioList
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
     * @return FormControl
     */
    protected function createComponentForm(): FormControl {
        $control = new FormControl();
        $form = $control->getForm();
        $form->addComponent($this->createPointsField(), 'points');
        $form->addSubmit('send', _('Uložit'));
        $form->onSuccess[] = function (Form $form) {
            $this->editFormSucceeded($form);
        };
        return $control;
    }

    /**
     * @param Form $form
     */
    private function editFormSucceeded(Form $form) {
        $values = $form->getValues();

        $submit = $this->submit;
        $this->serviceFyziklaniSubmit->updateModel($submit, [
            'points' => $values->points,
            /* ugly, exclude previous value of `modified` from query
             * so that `modified` is set automatically by DB
             * see https://dev.mysql.com/doc/refman/5.5/en/timestamp-initialization.html
             */
            'modified' => null
        ]);
        $this->serviceFyziklaniSubmit->save($submit);
        $this->getPresenter()->flashMessage(\sprintf(_('Body byly změněny: Tým "%s", body %d.'), $submit->getTeam()->name, $submit->points), \BasePresenter::FLASH_SUCCESS);

    }

    public function render() {
        $this->template->setFile(__DIR__ . DIRECTORY_SEPARATOR . 'EditSubmitControl.latte');
        $this->template->submit = $this->submit;
        $this->template->setTranslator($this->translator);
        $this->template->render();
    }
}
