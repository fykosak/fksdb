<?php

declare(strict_types=1);

namespace FKSDB\Modules\PublicModule;

use FKSDB\Models\ORM\Models\ContestantModel;
use FKSDB\Modules\Core\PresenterTraits\PresenterRole;
use FKSDB\Modules\Core\PresenterTraits\YearPresenterTrait;
use Fykosak\Utils\Localization\UnsupportedLanguageException;
use Nette\Application\BadRequestException;
use Nette\Application\ForbiddenRequestException;

abstract class BasePresenter extends \FKSDB\Modules\Core\BasePresenter
{
    use YearPresenterTrait;

    private ?ContestantModel $contestant;

    public function getContestant(): ?ContestantModel
    {
        if (!isset($this->contestant)) {
            $this->contestant = $this->getLoggedPerson()->getContestantByContestYear($this->getSelectedContestYear());
        }
        return $this->contestant;
    }

    /**
     * @throws UnsupportedLanguageException
     * @throws BadRequestException
     * @throws ForbiddenRequestException
     */
    protected function startup(): void
    {
        parent::startup();
        $this->yearTraitStartup();
    }

    /**
     * @return string[]
     */
    protected function getNavRoots(): array
    {
        return ['Public.Dashboard.default'];
    }

    protected function getStyleId(): string
    {
        $contest = $this->getSelectedContest();
        if (isset($contest)) {
            return 'contest-' . $contest->getContestSymbol();
        }
        return parent::getStyleId();
    }

    protected function getSubTitle(): ?string
    {
        return sprintf(_('%d. year'), $this->year);
    }

    protected function getRole(): PresenterRole
    {
        return PresenterRole::tryFrom(PresenterRole::CONTESTANT);
    }
}
