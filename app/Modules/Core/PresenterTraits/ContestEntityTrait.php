<?php

declare(strict_types=1);

namespace FKSDB\Modules\Core\PresenterTraits;

use FKSDB\Models\Entity\ModelNotFoundException;
use FKSDB\Models\Exceptions\GoneException;
use FKSDB\Models\ORM\Models\ContestModel;
use Fykosak\NetteORM\Exceptions\CannotAccessModelException;
use Fykosak\NetteORM\Model;
use Nette\Application\ForbiddenRequestException;

/**
 * @template M of (Model&Resource)
 */
trait ContestEntityTrait
{
    /** @phpstan-use EntityPresenterTrait<M> */
    use EntityPresenterTrait {
        getEntity as getBaseEntity;
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
        $model = $this->getBaseEntity();
        try {
            /** @var ContestModel $contest */
            $contest = $model->getReferencedModel(ContestModel::class);
            if ($contest->contest_id !== $this->getSelectedContest()->contest_id) {
                throw new ForbiddenRequestException(_('Editing entity outside chosen contest.'));
            }
        } catch (CannotAccessModelException $exception) {
            return $model;
        }
        return $model;
    }

    abstract protected function getSelectedContest(): ?ContestModel;
}
