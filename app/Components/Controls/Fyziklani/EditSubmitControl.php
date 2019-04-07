<?php

namespace FKSDB\Components\Controls\Fyziklani;

use FKSDB\Components\Controls\FormControl\FormControl;
use FKSDB\model\Fyziklani\ClosedSubmittingException;
use FKSDB\ORM\Models\Fyziklani\ModelFyziklaniSubmit;
use FKSDB\ORM\Models\ModelEvent;
use FKSDB\ORM\Services\Fyziklani\ServiceFyziklaniSubmit;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Control;
use Nette\Forms\Controls\RadioList;
use Nette\Forms\Form;
use Nette\Localization\ITranslator;
use Nette\Templating\FileTemplate;
use Tracy\Debugger;

/**
 * Class EditSubmitControl
 * @package FKSDB\Components\Controls\Fyziklani
 * @property FileTemplate $template
 */
class EditSubmitControl extends Control {
    use FKSDB\Components\Controls\Helpers\ValuePrintersTrait;
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
     * @var ITranslator
     */
    protected $translator;

    /**
     * EditSubmitControl constructor.
     * @param ModelEvent $event
     * @param ServiceFyziklaniSubmit $serviceFyziklaniSubmit
     * @param ITranslator $translator
     */
    public function __construct(ModelEvent $event, ServiceFyziklaniSubmit $serviceFyziklaniSubmit, ITranslator $translator) {
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
     * @param ModelFyziklaniSubmit $submit
     * @throws BadRequestException
     * @throws ClosedSubmittingException
     */
    public function setSubmit(ModelFyziklaniSubmit $submit) {
        $this->submit = $submit;
        if ($this->submit->canChange()) {
            /**
             * @var FormControl $control
             */
            $control = $this->getComponent('form');
            $control->getForm()->setDefaults([
                'team_id' => $this->submit->e_fyziklani_team_id,
                'points' => $this->submit->points,
            ]);
        } else {
            throw new ClosedSubmittingException($submit->getTeam());
        }
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
     * @throws BadRequestException
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
     * @throws \Nette\Application\AbortException
     */
    private function editFormSucceeded(Form $form) {
        $values = $form->getValues();
        try {
            $msg = $this->submit->changePoints($values->points);
            Debugger::log(\sprintf('fyziklani_submit %d edited by %d', $this->submit->fyziklani_submit_id, $this->getPresenter()->getUser()->getIdentity()->getPerson()->person_id));
            $this->getPresenter()->flashMessage($msg->getMessage(), $msg->getLevel());
            $this->redirect('this');
        } catch (ClosedSubmittingException $exception) {
            $this->getPresenter()->flashMessage($exception->getMessage(), \BasePresenter::FLASH_ERROR);
            $this->redirect('this');
        }
    }

    public function render() {
        $this->template->setFile(__DIR__ . DIRECTORY_SEPARATOR . 'EditSubmitControl.latte');
        $this->template->submit = $this->submit;
        $this->template->setTranslator($this->translator);
        $this->template->render();
    }
}
