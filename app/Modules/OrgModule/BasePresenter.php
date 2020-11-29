<?php

namespace FKSDB\Modules\OrgModule;

use FKSDB\Components\Controls\Choosers\ContestChooser;
use FKSDB\Components\Controls\Choosers\YearChooser;
use FKSDB\Modules\Core\ContestPresenter\ContestPresenter;
use FKSDB\Modules\Core\PresenterTraits\YearPresenterTrait;
use FKSDB\ORM\Models\ModelRole;

/**
 * Presenter keeps chosen contest, year and language in session.
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
abstract class BasePresenter extends ContestPresenter {
    use YearPresenterTrait;

    protected function startup(): void {
        $this->yearTraitStartup(YearChooser::ROLE_ORG);
        parent::startup();
    }

    protected function createComponentContestChooser(): ContestChooser {
        $control = new ContestChooser($this->getContext());
        $control->setContests(ModelRole::ORG);
        return $control;
    }

    protected function getNavRoots(): array {
        return ['Org.Dashboard.default'];
    }
}
