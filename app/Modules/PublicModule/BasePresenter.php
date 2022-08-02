<?php

declare(strict_types=1);

namespace FKSDB\Modules\PublicModule;

use FKSDB\Models\ORM\DbNames;
use FKSDB\Models\ORM\Models\ContestantModel;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Modules\Core\AuthenticatedPresenter;
use FKSDB\Modules\Core\PresenterTraits\YearPresenterTrait;

abstract class BasePresenter extends AuthenticatedPresenter
{
    use YearPresenterTrait;

    private ?ContestantModel $contestant;

    public function getContestant(): ?ContestantModel
    {
        if (!isset($this->contestant)) {
            /** @var PersonModel $person */
            $person = $this->user->getIdentity()->person;
            $row = $person->related(DbNames::TAB_CONTESTANT_BASE, 'person_id')->where(
                [
                    'contest_id' => $this->getSelectedContestYear()->contest_id,
                    'year' => $this->getSelectedContestYear()->year,
                ]
            )->fetch();

            $this->contestant = $row ? ContestantModel::createFromActiveRow($row) : null;
        }
        return $this->contestant;
    }

    protected function startup(): void
    {
        parent::startup();
        $this->yearTraitStartup();
    }

    protected function getNavRoots(): array
    {
        return ['Public.Dashboard.default'];
    }

    protected function beforeRender(): void
    {
        $contest = $this->getSelectedContest();
        if (isset($contest) && $contest) {
            $this->getPageStyleContainer()->styleIds[] = $contest->getContestSymbol();
            $this->getPageStyleContainer()->setNavBarClassName('navbar-dark bg-' . $contest->getContestSymbol());
            $this->getPageStyleContainer()->setNavBrandPath('/images/logo/white.svg');
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
