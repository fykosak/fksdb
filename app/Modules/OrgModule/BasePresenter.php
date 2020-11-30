<?php

namespace FKSDB\Modules\OrgModule;

use FKSDB\Components\Controls\Choosers\YearChooser;
use FKSDB\Modules\Core\AuthenticatedPresenter;
use FKSDB\Modules\Core\PresenterTraits\YearPresenterTrait;

/**
 * Presenter keeps chosen contest, year and language in session.
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
abstract class BasePresenter extends AuthenticatedPresenter {
    use YearPresenterTrait;

    protected function startup(): void {
        $this->yearTraitStartup(YearChooser::ROLE_ORG);
        parent::startup();
    }

    protected function getNavRoots(): array {
        return ['Org.Dashboard.default'];
    }
}
