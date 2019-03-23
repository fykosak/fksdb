<?php

namespace OrgModule;

use FKSDB\Components\Controls\FormControl\FormControl;
use FKSDB\Components\Forms\Containers\Models\ContainerWithOptions;
use FKSDB\Components\Grids\Validation\ValidationGrid;
use FKSDB\ORM\Models\ModelPerson;
use FKSDB\ORM\Services\ServicePerson;
use FKSDB\ValidationTest\Tests\ParticipantsDuration;
use FKSDB\ValidationTest\Tests\PhoneNumber;
use FKSDB\ValidationTest\ValidationLog;
use FKSDB\ValidationTest\ValidationTest;
use Nette\Diagnostics\Debugger;
use Nette\Forms\Form;

/**
 * Class ValidationPresenter
 * @package OrgModule
 */
class ValidationPresenter extends BasePresenter {

    /**
     * @var ServicePerson
     */
    private $servicePerson;
    /**
     * @var ValidationTest[]
     */
    private $tests = [PhoneNumber::class, ParticipantsDuration::class];

    /**
     * ValidationPresenter constructor.
     * @param ServicePerson $servicePerson
     */
    public function __construct(ServicePerson $servicePerson) {
        parent::__construct();
        $this->servicePerson = $servicePerson;
    }

    public function titleDefault() {
        $this->setTitle('Validačné testy');
    }

    /**
     * @return ValidationGrid
     */
    public function createComponentGrid(): ValidationGrid {
        return new ValidationGrid($this->servicePerson, [PhoneNumber::class, ParticipantsDuration::class]);
    }

    /**
     * @return FormControl
     * @throws \Nette\Application\BadRequestException
     */
    public function createComponentLevelForm() {
        $control = new FormControl();
        $form = $control->getForm();
        $form->addText('limit', _('Limit'))->addRule(Form::INTEGER, _('Limit musí byť číslo'));
        $container = new ContainerWithOptions();
        $form->addComponent($container, 'levels');
        $container->addCheckbox('danger', _('danger'));
        $container->addCheckbox('warning', _('warning'));
        $container->addCheckbox('info', _('info'));
        $container->addCheckbox('success', _('success'));
        $form->addSubmit('submit');
        $form->onSuccess[] = function (Form $form) {
            Debugger::barDump($form->getValues());
        };
        return $control;
    }

    public function renderErrors() {

        $query = $this->servicePerson->getTable()->page(1, 100);

        $logs = [];
        foreach ($query as $row) {

            $model = ModelPerson::createFromTableRow($row);
            $personLog = [];
            foreach ($this->tests as $test) {
                $log = \array_filter($test::run($model), function (ValidationLog $simpleLog) {
                    return $simpleLog->level === 'danger';
                });
                $personLog = \array_merge($personLog, $log);
            }
            if (\count($personLog)) {
                $logs[] = ['model' => $model, 'log' => $personLog];
            }
        }
        $this->template->logs = $logs;
    }

}


