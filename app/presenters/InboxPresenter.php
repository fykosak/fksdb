<?php

/**
 * Homepage presenter.
 */
class InboxPresenter extends AuthenticatedPresenter {

    public function renderDefault() {
        $this->template->contestants = $this->getContestants(1,1)->fetchPairs('ct_id');
    }

    protected function createComponentInboxForm($name) {
        $form = new NAppForm($this, $name);

        $contest_id = 1;
        $year = 1;
        $series = 1;

        $contestants = $this->getContestants($contest_id, $year);
        $tasks = $this->getTasks($contest_id, $year, $series);

        $serviceSubmit = $this->context->getService('ServiceSubmit');
        $submits = $serviceSubmit->where('ct_id', $contestants)->where('task_id', $tasks);

        // store submits in 2D hash for better access
        $submitTable = array();
        foreach ($submits as $submit) {
            if (!isset($submitTable[$submit->ct_id])) {
                $submitTable[$submit->ct_id] = array();
            }
            $submitTable[$submit->ct_id][$submit->task_id] = $submit;
        }

        $grid = $form->addContainer('grid');
        foreach ($contestants as $contestant) {
            $container = $grid->addContainer($contestant->ct_id);

            foreach ($tasks as $task) {
                $subcontainer = $container->addContainer($task->task_id);
                $text = $subcontainer->addText('submitted_on');
                $note = $subcontainer->addText('note');
                if (isset($submitTable[$contestant->ct_id]) && isset($submitTable[$contestant->ct_id][$task->task_id])) {
                    $text->setDefaultValue($submitTable[$contestant->ct_id][$task->task_id]->submitted_on);
                }
            }
        }
    }

    protected function getContestants($contest_id, $year) {
        $serviceContestant = $this->context->getService('ServiceContestant');
        return $serviceContestant->where(array(
                    'contest_id' => $contest_id,
                    'year' => $year
                ))->order('person.last_name, person.first_name');
    }

    protected function getTasks($contest_id, $year, $series) {
        $serviceTask = $this->context->getService('ServiceTask');
        return $serviceTask->where(array(
                    'contest_id' => $contest_id,
                    'year' => $year,
                    'series' => $series
                ))->order('tasknr');
    }

}
