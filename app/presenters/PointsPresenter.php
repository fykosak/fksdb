<?php

use Nette\Application\UI\Form;

class PointsPresenter extends TaskTimesContestantPresenter {

    public function renderDefault() {
        $this->template->contestants = $this->getContestants($this->getSeries());
        $this->template->tasks = $this->getTasks()->fetchPairs('task_id');
    }

    protected function createComponentPointsForm($name) {
        $form = new Form($this, $name);

        $contestants = $this->getContestants($this->getSeries());
        $tasks = $this->getTasks();
        $submitsTable = $this->getSubmitsTable();

        $grid = $form->addContainer('grid');
        $ct_i = 0;
        $ct_cnt = count($contestants);
        $t_cnt = count($tasks);
        foreach ($contestants as $contestant) {
            $container = $grid->addContainer($contestant->ct_id);

            $t_i = 0;
            foreach ($tasks as $task) {
                $subcontainer = $container->addContainer($task->task_id);
                $text = $subcontainer->addHidden('submitted_on');
                $note = $subcontainer->addHidden('note');
                $points = $subcontainer->addText('raw_points', null, 1);
                $points->addCondition(Form::FILLED)
                        ->addRule(Form::NUMERIC, 'Počet bodů má být přirozené číslo.');
                $points->getControlPrototype()->tabindex($ct_i + $ct_cnt * $t_i + 1);


                if (isset($submitsTable[$contestant->ct_id][$task->task_id])) {
                    $submit = $submitsTable[$contestant->ct_id][$task->task_id];
                    $text->setDefaultValue($submit->submitted_on);
                    $note->setDefaultValue($submit->note);
                    $points->setDefaultValue(($submit->raw_points !== null) ? (int) $submit->raw_points : '');
                } else {
                    $points->setDisabled(true);
                }
                $t_i += 1;
            }
            $ct_i += 1;
        }

        $submit = $form->addSubmit('save', 'Uložit');
        $submit->getControlPrototype()->tabindex($t_cnt * $ct_cnt + 1);

        $form->onSuccess[] = array($this, 'pointsFormSuccess');
    }

    public function pointsFormSuccess(Form $form) {
        $values = $form->getValues();
        $grid = $values['grid'];
        $submitsTable = $this->getSubmitsTable();
        $serviceSubmit = $this->context->getService('ServiceSubmit');
        $serviceSubmit->getConnection()->beginTransaction();

        foreach ($grid as $ct_id => $tasks) {
            foreach ($tasks as $task_id => $elements) {
                if (!isset($submitsTable[$ct_id][$task_id])) {
                    continue;
                }
                $submit = $submitsTable[$ct_id][$task_id];

                $submit->raw_points = $elements['raw_points'] === '' ? null : $elements['raw_points'];

                $serviceSubmit->save($submit);
            }
        }
        $serviceSubmit->getConnection()->commit();
        $this->flashMessage('Body úloh uloženy.');
        $this->redirect('this');
    }

}
