<?php

namespace OrgModule;

use ContestPresenter;
use FKSDB\Components\Controls\ContestChooser;
use FKSDB\ORM\Models\ModelRole;

/**
 * Presenter keeps chosen contest, year and language in session.
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
abstract class BasePresenter extends ContestPresenter {


    /**
     * @return ContestChooser
     */
    protected function createComponentContestChooser(): ContestChooser {
        $control = new ContestChooser($this->session, $this->yearCalculator, $this->serviceContest);
        $control->setContests(ModelRole::ORG);
        return $control;
    }

    /**
     * @return string[]
     */
    public function getNavRoots(): array {
        return ['org.dashboard.default'];
    }
}
