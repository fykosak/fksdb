<?php

declare(strict_types=1);

namespace FKSDB\Modules\Core\PresenterTraits;

use FKSDB\Models\Entity\ModelNotFoundException;
use FKSDB\Models\Exceptions\GoneException;
use FKSDB\Models\ORM\Models\ContestYearModel;
use Fykosak\NetteORM\Exceptions\CannotAccessModelException;
use Fykosak\NetteORM\Model;
use Nette\Application\ForbiddenRequestException;

/**
 * @template CYM of (Model&\Nette\Security\Resource)
 */
trait ContestYearEntityTrait
{
    /** @phpstan-use ContestEntityTrait<CYM> */
    use ContestEntityTrait {
        getEntity as getContestEntity;
    }

    /**
     * @return CYM
     * @throws ForbiddenRequestException
     * @throws ModelNotFoundException
     * @throws GoneException
     * @throws \ReflectionException
     * @throws CannotAccessModelException
     */
    protected function getEntity(): Model
    {
        /** @var CYM $model */
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
