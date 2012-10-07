<?php

/**
 * Homepage presenter.
 */
class InboxPresenter extends AuthenticatedPresenter {

    public function renderDefault() {
        $this->template->contestants = $this->getContestants(1, 1)->fetchPairs('ct_id');
        $this->template->tasks = $this->getTasks(1, 1, 1)->fetchPairs('task_id');
    }

    protected function createComponentInboxForm($name) {
        $form = new NAppForm($this, $name);

        $contest_id = 1;
        $year = 1;
        $series = 1;

        $contestants = $this->getContestants($contest_id, $year);
        $tasks = $this->getTasks($contest_id, $year, $series);
        $submitsTable = $this->getSubmitsTable($contest_id, $year, $series);

        $grid = $form->addContainer('grid');
        foreach ($contestants as $contestant) {
            $container = $grid->addContainer($contestant->ct_id);

            foreach ($tasks as $task) {
                $subcontainer = $container->addContainer($task->task_id);
                $text = $subcontainer->addText('submitted_on');
                $note = $subcontainer->addText('note');
                if (isset($submitsTable[$contestant->ct_id][$task->task_id])) {
                    $text->setDefaultValue($submitsTable[$contestant->ct_id][$task->task_id]->submitted_on);
                    $note->setDefaultValue($submitsTable[$contestant->ct_id][$task->task_id]->note);
                }
            }
        }

        $form->addSubmit('save', 'Uložit');
        $form->onSuccess[] = array($this, 'inboxFormSuccess');
    }

    public function inboxFormSuccess(NAppForm $form) {
        $values = $form->getValues();
        $grid = $values['grid'];
        $submitsTable = $this->getSubmitsTable(1, 1, 1);
        $serviceSubmit = $this->context->getService('ServiceSubmit');
        $serviceSubmit->getConnection()->beginTransaction();

        foreach ($grid as $ct_id => $tasks) {
            foreach ($tasks as $task_id => $elements) {
                if (isset($submitsTable[$ct_id][$task_id])) { // is in the table
                    $submit = $submitsTable[$ct_id][$task_id];
                } else {
                    $submit = $serviceSubmit->createNew(array(
                        'ct_id' => $ct_id,
                        'task_id' => $task_id,
                        'source' => ModelSubmit::SOURCE_POST,
                            ));
                }
                
                $submit->note = $elements['note'];
                if ($submit->source != ModelSubmit::SOURCE_UPLOAD) {
                    $submit->submitted_on = $elements['submitted_on'];
                }

                if ($submit->isEmpty() && $submit->source != ModelSubmit::SOURCE_UPLOAD) {
                    $serviceSubmit->dispose($submit);
                } else {
                    $serviceSubmit->save($submit);
                }
            }
        }
        $serviceSubmit->getConnection()->commit();
        $this->flashMessage('Přijatá řešení uložena.');
        $this->redirect('this');
    }

    protected function getContestants($contest_id, $year) {
        $serviceContestant = $this->context->getService('ServiceContestant');
        return $serviceContestant->getTable()->where(array(
                    'contest_id' => $contest_id,
                    'year' => $year
                ))->order('person.last_name, person.first_name');
    }

    protected function getTasks($contest_id, $year, $series) {
        $serviceTask = $this->context->getService('ServiceTask');
        return $serviceTask->getTable()->where(array(
                    'contest_id' => $contest_id,
                    'year' => $year,
                    'series' => $series
                ))->order('tasknr');
    }

    protected function getSubmitsTable($contest_id, $year, $series) {
        $serviceSubmit = $this->context->getService('ServiceSubmit');

        $submits = $serviceSubmit->getTable()
                ->where('ct_id', $this->getContestants($contest_id, $year))
                ->where('task_id', $this->getTasks($contest_id, $year, $series));

        // store submits in 2D hash for better access
        $submitsTable = array();
        foreach ($submits as $submit) {
            if (!isset($submitsTable[$submit->ct_id])) {
                $submitsTable[$submit->ct_id] = array();
            }
            $submitsTable[$submit->ct_id][$submit->task_id] = ModelSubmit::createFromTableRow($submit);
        }
        return $submitsTable;
    }

}
