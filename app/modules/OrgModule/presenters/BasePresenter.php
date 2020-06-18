<?php

namespace FKSDB\OrgModule;

use FKSDB\Components\Controls\ContestChooser;
use FKSDB\ORM\Models\ModelRole;

/**
 * Presenter keeps chosen contest, year and language in session.
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
abstract class BasePresenter extends \FKSDB\CoreModule\ContestPresenter {

    protected function createComponentContestChooser(): ContestChooser {
        $control = new ContestChooser($this->getContext());
        $control->setContests(ModelRole::ORG);
        return $control;
    }

    /**
     * @return string[]
     */
    public function getNavRoots(): array {
        return ['Org.Dashboard.default'];
    }
}
