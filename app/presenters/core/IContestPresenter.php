<?php

/*
 * For presenters that provide contest and year context.
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
interface IContestPresenter {

    /** @return ModelContest */
    public function getSelectedContest();

    /** @return int */
    public function getSelectedYear();

    /** @return int */
    public function getSelectedAcademicYear();
}

