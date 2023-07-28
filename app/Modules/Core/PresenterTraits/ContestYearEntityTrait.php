<?php

declare(strict_types=1);

namespace FKSDB\Modules\Core\PresenterTraits;

use FKSDB\Models\Entity\ModelNotFoundException;
use FKSDB\Models\Exceptions\GoneException;
use FKSDB\Models\ORM\Models\ContestYearModel;
use Fykosak\NetteORM\Exceptions\CannotAccessModelException;
use Fykosak\NetteORM\Model;
use Nette\Application\ForbiddenRequestException;
use Tracy\Debugger;

/**
 * @template M of (Model&Resource)
 */
trait ContestYearEntityTrait
{
    /** @phpstan-use ContestEntityTrait<M> */
    use ContestEntityTrait {
        getEntity as getContestEntity;
    }

    /**
     * @throws CannotAccessModelException
     * @throws ForbiddenRequestException
     * @throws ModelNotFoundException
     * @throws GoneException
     * @throws \ReflectionException
     * @phpstan-return M
     */
    protected function getEntity(): Model
    {
        $model = $this->getContestEntity();
        try {
            /** @var ContestYearModel $contestYear */
            $contestYear = $model->getReferencedModel(ContestYearModel::class);
            Debugger::barDump($contestYear);
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
