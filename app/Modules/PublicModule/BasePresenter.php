<?php

namespace FKSDB\Modules\PublicModule;

use FKSDB\Modules\Core\AuthenticatedPresenter;
use FKSDB\Modules\Core\PresenterTraits\YearPresenterTrait;
use FKSDB\Model\ORM\DbNames;
use FKSDB\Model\ORM\Models\ModelContestant;
use FKSDB\Model\ORM\Models\ModelPerson;
use FKSDB\Model\UI\PageTitle;

/**
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
abstract class BasePresenter extends AuthenticatedPresenter {
    use YearPresenterTrait;

    private ?ModelContestant $contestant;

    protected function startup(): void {
        $this->yearTraitStartup();
        parent::startup();
    }

    public function getContestant(): ?ModelContestant {
        if (!isset($this->contestant)) {
            /** @var ModelPerson $person */
            $person = $this->user->getIdentity()->getPerson();
            $contestant = $person->related(DbNames::TAB_CONTESTANT_BASE, 'person_id')->where([
                'contest_id' => $this->getSelectedContest()->contest_id,
                'year' => $this->getSelectedYear(),
            ])->fetch();

            $this->contestant = $contestant ? ModelContestant::createFromActiveRow($contestant) : null;
        }
        return $this->contestant;
    }

    protected function getNavRoots(): array {
        return ['Public.Dashboard.default'];
    }

    protected function beforeRender(): void {
        $contest = $this->getSelectedContest();
        if (isset($contest) && $contest) {
            $this->getPageStyleContainer()->styleId = $contest->getContestSymbol();
            $this->getPageStyleContainer()->setNavBarClassName('navbar-dark bg-' . $contest->getContestSymbol());
        }
        parent::beforeRender();
    }

    protected function setPageTitle(PageTitle $pageTitle): void {
        $pageTitle->subTitle = sprintf(_('%d. year'), $this->year) . ' ' . $pageTitle->subTitle;
        parent::setPageTitle($pageTitle);
    }

    protected function getRole(): string {
        return 'contestant';
    }
}
