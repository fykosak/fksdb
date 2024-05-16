<?php

declare(strict_types=1);

namespace FKSDB\Components\Grids\Spam;

use FKSDB\Components\Grids\Components\BaseGrid;
use FKSDB\Models\ORM\Models\Spam\SpamSchoolModel;
use FKSDB\Models\ORM\Services\Spam\SpamSchoolService;
use Fykosak\NetteORM\Selection\TypedSelection;
use Fykosak\Utils\UI\Title;

/**
 * @phpstan-extends BaseGrid<SpamSchoolModel,array{}>
 */
final class SchoolGrid extends BaseGrid
{
    private SpamSchoolService $service;

    public function inject(SpamSchoolService $service): void
    {
        $this->service = $service;
    }

    /**
     * @phpstan-return TypedSelection<SpamSchoolModel>
     */
    protected function getModels(): TypedSelection
    {
        return $this->service->getTable();
    }

    protected function configure(): void
    {
        $this->paginate = true;
        $this->counter = true;
        $this->filtered = false;
        $this->addSimpleReferencedColumns([
            '@spam_school.spam_school_label',
            '@school.school',
        ]);
        $this->addPresenterButton(
            'edit',
            'edit',
            new Title(null, _('button.edit')),
            false,
            ['id' => 'spam_school_label']
        );
    }
}
