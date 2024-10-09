<?php

declare(strict_types=1);

namespace FKSDB\Modules\Core\PresenterTraits;

use FKSDB\Models\Authorization\Resource\ContestResource;
use FKSDB\Models\Exceptions\GoneException;
use FKSDB\Models\Exceptions\NotFoundException;
use FKSDB\Models\ORM\Models\ContestModel;
use Fykosak\NetteORM\Exceptions\CannotAccessModelException;
use Fykosak\NetteORM\Model\Model;
use Nette\Application\ForbiddenRequestException;

/**
 * @phpstan-template TContestModel of (Model&ContestResource)
 */
trait ContestEntityTrait
{
    /** @phpstan-use EntityPresenterTrait<TContestModel> */
    use EntityPresenterTrait {
        getEntity as getBaseEntity;
    }

    /**
     * @phpstan-return TContestModel
     * @throws ForbiddenRequestException
     * @throws NotFoundException
     * @throws GoneException
     * @throws \ReflectionException
     * @throws CannotAccessModelException
     * @throws NoContestAvailable
     */
    protected function getEntity(): Model
    {
        /** @phpstan-var TContestModel $model */
        $model = $this->getBaseEntity();
        try {
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
