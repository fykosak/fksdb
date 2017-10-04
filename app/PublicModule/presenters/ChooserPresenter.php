<?php
/**
 * Created by IntelliJ IDEA.
 * User: miso
 * Date: 11.9.2017
 * Time: 3:44
 */

namespace PublicModule;

use AuthenticatedPresenter;
use FKSDB\Components\Controls\LanguageChooser;
use IContestPresenter;
use Nette\Application\BadRequestException;
use Nette\Diagnostics\Debugger;

class ChooserPresenter extends AuthenticatedPresenter implements IContestPresenter {

    const PRESETS_KEY = 'publicPresets';

    /**
     * @persistent
     */
    public $contestId;

    /**
     * @var int
     * @persistent
     */
    public $year;

    /**
     * @persistent
     */
    public $lang;

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

    public function handleChange($contestId, $role) {
        switch ($role) {
            case 'org':
                $this->redirect(':Org:Dashboard:default', ['contestId' => $contestId,]);
                return;
            case 'contestant':
                $this->redirect(':Public:Dashboard:default', ['contestId' => $contestId,]);
                return;
        }
    }

    public function getTitle() {
        return _('Razcestník');
    }

    public function getSelectedContestSymbol() {
        return null;
    }

}