<?php

declare(strict_types=1);

namespace FKSDB\Components\Grids;

use FKSDB\Components\Grids\Components\BaseGrid;
use FKSDB\Models\ORM\Models\PersonMailModel;
use FKSDB\Models\ORM\Services\PersonMailService;
use Fykosak\NetteORM\Selection\TypedSelection;
use Fykosak\Utils\UI\Title;

/**
 * @phpstan-extends BaseGrid<PersonMailModel,array{}>
 */
final class MailGrid extends BaseGrid
{
    private PersonMailService $service;

    public function inject(PersonMailService $service): void
    {
        $this->service = $service;
    }

    /**
     * @phpstan-return TypedSelection<PersonMailModel>
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
            '@person_mail.person_mail_id',
            '@person.full_name',
            '@person_mail.mail_type',
        ]);
    }
}
