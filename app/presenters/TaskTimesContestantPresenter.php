<?php

/**
 * Presenter "template" for presenters manipulating tasks x contestants tables.
 * 
 */
abstract class TaskTimesContestantPresenter extends AuthenticatedPresenter {

    /**
     * @param int $series when not null return only contestants with submits in the series
     * @return Nette\Database\Table\Selection
     */
    protected function getContestants($series = null) {
        $serviceContestant = $this->context->getService('ServiceContestant');
        return $serviceContestant->getTable()->where(array(
                    'contest_id' => $this->getSelectedContest()->contest_id,
                    'year' => $this->getSelectedYear(),
                ))->order('person.sort_name, person.display_name');
        //TODO series
    }

    protected function getTasks() {
        $serviceTask = $this->context->getService('ServiceTask');
        return $serviceTask->getTable()->where(array(
                    'contest_id' => $this->getSelectedContest()->contest_id,
                    'year' => $this->getSelectedYear(),
                    'series' => $this->getSeries(),
                ))->order('tasknr');
    }

    /**
     * 
     * @return int
     */
    protected function getSeries() {
        return 1; //TODO
    }

    protected function getSubmitsTable() {
        $serviceSubmit = $this->context->getService('ServiceSubmit');

        $submits = $serviceSubmit->getTable()
                ->where('ct_id', $this->getContestants())
                ->where('task_id', $this->getTasks());

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
