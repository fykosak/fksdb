<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Tests\School;

use FKSDB\Models\ORM\Tests\Adapter;
use FKSDB\Models\ORM\Models\SchoolModel;
use FKSDB\Models\ORM\Services\SchoolService;
use Fykosak\NetteORM\Model\Model;

/**
 * @phpstan-extends Adapter<never,SchoolModel>
 */
class SchoolsProviderAdapter extends Adapter
{
    private SchoolService $service;

    public function injectSchoolService(SchoolService $service): void
    {
        $this->service = $service;
    }

    protected function getModels(Model $model): iterable
    {
        return $this->service->getTable(); //@phpstan-ignore-line
    }

    protected function getLogPrepend(Model $model): string
    {
        return sprintf(_('In school %s(%d): '), $model->name_full, $model->school_id);
    }

    public function getId(): string
    {
        return 'Schools';
    }
}
