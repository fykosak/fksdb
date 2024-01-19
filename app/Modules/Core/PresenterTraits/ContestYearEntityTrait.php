<?php

declare(strict_types=1);

namespace FKSDB\Modules\Core\PresenterTraits;

use FKSDB\Models\Exceptions\GoneException;
use FKSDB\Models\Exceptions\NotFoundException;
use FKSDB\Models\ORM\Models\ContestYearModel;
use Fykosak\NetteORM\Exceptions\CannotAccessModelException;
use Fykosak\NetteORM\Model\Model;
use Nette\Application\ForbiddenRequestException;

/**
 * @phpstan-template TContestYearModel of (Model&\Nette\Security\Resource)
 */
trait ContestYearEntityTrait
{
    /** @phpstan-use ContestEntityTrait<TContestYearModel> */
    use ContestEntityTrait {
        getEntity as getContestEntity;
    }

    /**
     * @phpstan-return TContestYearModel
     * @throws ForbiddenRequestException
     * @throws NotFoundException
     * @throws GoneException
     * @throws \ReflectionException
     * @throws CannotAccessModelException
     * @throws NoContestAvailable
     * @throws NoContestYearAvailable
     */
    protected function getEntity(): Model
    {
        /** @phpstan-var TContestYearModel $model */
        $model = $this->getContestEntity();
        try {
            /** @var ContestYearModel $contestYear */
            $contestYear = $model->getReferencedModel(ContestYearModel::class);
            if ($contestYear->year !== $this->getSelectedContestYear()->year) {
                throw new ForbiddenRequestException(_('Editing entity outside chosen year.'));
            }
        } catch (CannotAccessModelException $exception) {
            return $model;
        }
        return $model;
    }

    abstract protected function getSelectedContestYear(): ?ContestYearModel;
}
