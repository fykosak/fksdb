<?php

declare(strict_types=1);

namespace FKSDB\Modules\PublicModule;

use FKSDB\Modules\Core\AuthenticatedPresenter;
use FKSDB\Modules\Core\PresenterTraits\YearPresenterTrait;
use FKSDB\Models\ORM\DbNames;
use FKSDB\Models\ORM\Models\ModelContestant;
use FKSDB\Models\ORM\Models\ModelPerson;

abstract class BasePresenter extends AuthenticatedPresenter
{
    use YearPresenterTrait;

    private ?ModelContestant $contestant;

    protected function startup(): void
    {
        parent::startup();
        $this->yearTraitStartup();
    }

    public function getContestant(): ?ModelContestant
    {
        if (!isset($this->contestant)) {
            /** @var ModelPerson $person */
            $person = $this->user->getIdentity()->getPerson();
            $row = $person->related(DbNames::TAB_CONTESTANT_BASE, 'person_id')->where([
                'contest_id' => $this->getSelectedContestYear()->contest_id,
                'year' => $this->getSelectedContestYear()->year,
            ])->fetch();

            $this->contestant = $row ? ModelContestant::createFromActiveRow($row) : null;
        }
        return $this->contestant;
    }

    protected function getNavRoots(): array
    {
        return ['Public.Dashboard.default'];
    }

    protected function beforeRender(): void
    {
        $contest = $this->getSelectedContest();
        if (isset($contest) && $contest) {
            $this->getPageStyleContainer()->styleId = $contest->getContestSymbol();
            $this->getPageStyleContainer()->setNavBarClassName('navbar-dark bg-' . $contest->getContestSymbol());
        }
        parent::beforeRender();
    }

    protected function getDefaultSubTitle(): ?string
    {
        return sprintf(_('%d. year'), $this->year);
    }

    protected function getRole(): string
    {
        return 'contestant';
    }
}
