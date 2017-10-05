<?php

class ChooserPresenter extends AuthenticatedPresenter implements IContestPresenter {

    public function getSelectedContest() {
        return null;
    }

    public function getSelectedYear() {
        return null;
    }

    public function getSelectedAcademicYear() {
        return null;
    }

    public function getSelectedSeries() {
        return null;
    }

    public function handleChangeContest($contestId, $role) {
        switch ($role) {
            case 'org':
                $this->redirect(':Org:Dashboard:default', [
                    'contestId' => $contestId,
                ]);
                return;
            case 'contestant':
                $this->redirect(':Public:Dashboard:default', [
                    'contestId' => $contestId,
                ]);
                return;
        }
    }

    public function getTitle() {
        return _('Razcestník');
    }

    public function getSelectedContestSymbol() {
        return null;
    }

    public function getNavRoot() {
        return null;
    }


}