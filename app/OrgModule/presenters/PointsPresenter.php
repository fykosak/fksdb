<?php

namespace OrgModule;

use Exception;
use ModelContest;
use Nette\Application\UI\Form;
use Nette\Diagnostics\Debugger;

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

        try {
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

            // recalculate points (separate transaction)
            $SQLcache = $this->getService('SQLResultsCache');
            $SQLcache->recalculate($this->getSelectedContest(), $this->getSelectedYear());


            $this->flashMessage('Body úloh uloženy.');
        } catch (Exception $e) {
            $this->flashMessage('Chyba při ukládání bodů.', 'error');
            Debugger::log($e);
        }
        $this->redirect('this');
    }

    public function handleInvalidate() {
        $SQLcache = $this->getService('SQLResultsCache');

        try {
            $SQLcache->invalidate($this->getSelectedContest(), $this->getSelectedYear());
            $this->flashMessage('Body invalidovány.');
        } catch (Exception $e) {
            $this->flashMessage('Chyba při invalidaci.', 'error');
            Debugger::log($e);
        }

        $this->redirect('this');
    }

    public function handleRecalculateAll() {
        $SQLcache = $this->getService('SQLResultsCache');
        $serviceTask = $this->context->getService('ServiceTask');
        
        try {
            foreach ($this->getAvailableContests() as $contest) {
                $contest = ModelContest::createFromTableRow($contest);

                $years = $serviceTask->getTable()
                                ->select('year')
                                ->where(array(
                                    'contest_id' => $contest->contest_id,
                                ))->group('year');

                foreach ($years as $year) {
                    $SQLcache->recalculate($contest, $year->year);
                }
            }

            $this->flashMessage('Body přepočítány.');
        } catch (Exception $e) {
            $this->flashMessage('Chyba při přepočtu.', 'error');
            Debugger::log($e);
        }

        $this->redirect('this');
    }

}
