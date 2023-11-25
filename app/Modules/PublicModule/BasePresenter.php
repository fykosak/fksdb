<?php

declare(strict_types=1);

namespace FKSDB\Modules\PublicModule;

use FKSDB\Models\Exceptions\NotFoundException;
use FKSDB\Models\ORM\Models\ContestantModel;
use FKSDB\Modules\Core\PresenterTraits\NoContestAvailable;
use FKSDB\Modules\Core\PresenterTraits\NoContestYearAvailable;
use FKSDB\Modules\Core\PresenterTraits\PresenterRole;
use FKSDB\Modules\Core\PresenterTraits\YearPresenterTrait;
use Fykosak\Utils\Localization\UnsupportedLanguageException;
use Nette\Application\BadRequestException;
use Nette\Application\ForbiddenRequestException;

abstract class BasePresenter extends \FKSDB\Modules\Core\BasePresenter
{
    use YearPresenterTrait;

    /**
     * @throws NoContestAvailable
     * @throws NotFoundException
     * @throws NoContestYearAvailable
     */
    public function getContestant(): ContestantModel
    {
        static $contestant;
        if (!isset($contestant)) {
            $contestant = $this->getLoggedPerson()->getContestant($this->getSelectedContestYear());
        }
        if (!$contestant) {
            throw new NotFoundException(_('Contestant not found'));
        }
        return $contestant;
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
     * @phpstan-return string[]
     */
    protected function getNavRoots(): array
    {
        return ['Public.Dashboard.default'];
    }

    /**
     * @throws NoContestAvailable
     */
    protected function getStyleId(): string
    {
        $contest = $this->getSelectedContest();
        return 'contest-' . $contest->getContestSymbol();
    }

    protected function getSubTitle(): ?string
    {
        return sprintf(_('%d. year'), $this->year);
    }

    protected function getRole(): PresenterRole
    {
        return PresenterRole::from(PresenterRole::CONTESTANT);
    }
}
