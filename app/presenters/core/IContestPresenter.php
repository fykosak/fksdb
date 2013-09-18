<?php

/*
 * For presenters that provide current contest and year information.
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
interface IContestPresenter {

    /** @return ModelContest */
    public function getSelectedContest();

    /** @return int */
    public function getSelectedYear();
}

?>
