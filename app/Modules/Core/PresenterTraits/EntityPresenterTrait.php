<?php

declare(strict_types=1);

namespace FKSDB\Modules\Core\PresenterTraits;

use FKSDB\Models\Exceptions\GoneException;
use FKSDB\Models\Exceptions\NotFoundException;
use Fykosak\NetteORM\Model\Model;
use Fykosak\NetteORM\Service\Service;
use Nette\Security\Resource;

/**
 * @phpstan-template TModel of (Model&Resource)
 */
trait EntityPresenterTrait
{
    /**
     * @persistent
     */
    public ?int $id = null;

    /**
     * @phpstan-return TModel
     * @throws GoneException
     * @throws NotFoundException
     */
    public function getEntity(): Model
    {
        static $model;
        // protection for tests ev . change URL during app is running
        if (!isset($model) || $this->id !== $model->getPrimary()) {
            $model = $this->loadModel();
        }
        return $model;
    }

    /**
     * @throws NotFoundException
     * @throws GoneException
     * @phpstan-return TModel
     */
    protected function loadModel(): Model
    {
        /** @phpstan-var TModel|null $candidate */
        $candidate = $this->getORMService()->findByPrimary($this->id);
        if ($candidate) {
            return $candidate;
        } else {
            throw new NotFoundException(_('Model does not exist.'));
        }
    }


    /**
     * @phpstan-return Service<TModel>
     */
    abstract protected function getORMService(): Service;
}
