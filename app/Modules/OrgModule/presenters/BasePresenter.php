<?php

namespace FKSDB\Modules\OrgModule;

use FKSDB\Components\Controls\Choosers\ContestChooser;
use FKSDB\Modules\Core\ContestPresenter\ContestPresenter;
use FKSDB\ORM\Models\ModelRole;

/**
 * Presenter keeps chosen contest, year and language in session.
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
abstract class BasePresenter extends ContestPresenter {

    protected function createComponentContestChooser(): ContestChooser {
        $control = new ContestChooser($this->getContext());
        $control->setContests(ModelRole::ORG);
        return $control;
    }

    protected function getNavRoots(): array {
        return ['Org.Dashboard.default'];
    }
}
