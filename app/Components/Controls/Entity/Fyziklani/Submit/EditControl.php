<?php

namespace FKSDB\Components\Controls\Entity\Fyziklani\Submit;

use FKSDB\Components\Controls\Entity\IEditEntityForm;
use FKSDB\Components\Controls\FormControl\FormControl;
use FKSDB\Fyziklani\ClosedSubmittingException;
use FKSDB\Fyziklani\NotSetGameParametersException;
use FKSDB\ORM\AbstractModelSingle;
use FKSDB\ORM\Models\Fyziklani\ModelFyziklaniSubmit;
use FKSDB\ORM\Models\ModelEvent;
use FKSDB\ORM\Services\Fyziklani\ServiceFyziklaniSubmit;
use Nette\Application\AbortException;
use Nette\Application\BadRequestException;
use Nette\DI\Container;
use Nette\Forms\Controls\RadioList;
use Nette\Forms\Form;
use Nette\Localization\ITranslator;

/**
 * Class EditControl
 * @author Michal Červeňák <miso@fykos.cz>
 */
class EditControl extends FormControl implements IEditEntityForm {
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
     * EditControl constructor.
     * @param Container $container
     * @param ModelEvent $event
     * @throws BadRequestException
     */
    public function __construct(Container $container, ModelEvent $event) {
        parent::__construct($container);
        $this->serviceFyziklaniSubmit = $container->getByType(ServiceFyziklaniSubmit::class);
        $this->translator = $container->getByType(ITranslator::class);
        $this->event = $event;

        $form = $this->getForm();
        $form->addComponent($this->createPointsField(), 'points');
        $form->addSubmit('send', _('Save'));
        $form->onSuccess[] = function (Form $form) {
            $this->editFormSucceeded($form);
        };
    }


    /**
     * @param AbstractModelSingle|ModelFyziklaniSubmit $submit
     * @throws BadRequestException
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
    private function editFormSucceeded(Form $form) {
        $values = $form->getValues();
        try {
            $msg = $this->serviceFyziklaniSubmit->changePoints($this->submit, $values->points, $this->getPresenter()->getUser());
            $this->getPresenter()->flashMessage($msg->getMessage(), $msg->getLevel());
            $this->redirect('this');
        } catch (ClosedSubmittingException $exception) {
            $this->getPresenter()->flashMessage($exception->getMessage(), \BasePresenter::FLASH_ERROR);
            $this->redirect('this');
        }
    }
}
