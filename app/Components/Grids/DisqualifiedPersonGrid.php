<?php

declare(strict_types=1);

namespace FKSDB\Components\Grids;

use FKSDB\Components\Forms\Controls\DateInputs\DateInput;
use FKSDB\Components\Grids\Components\BaseGrid;
use FKSDB\Models\ORM\Models\DisqualifiedPersonModel;
use FKSDB\Models\ORM\Services\DisqualifiedPersonService;
use Fykosak\NetteORM\Selection\TypedSelection;
use Nette\Forms\Form;
use Nette\Utils\DateTime;

/**
 * @phpstan-extends BaseGrid<DisqualifiedPersonModel,array{date?:DateTime|null}>
 */
final class DisqualifiedPersonGrid extends BaseGrid
{
    private DisqualifiedPersonService $disqualifiedPersonService;

    public function injectService(DisqualifiedPersonService $disqualifiedPersonService): void
    {
        $this->disqualifiedPersonService = $disqualifiedPersonService;
    }

    protected function configure(): void
    {
        $this->filtered = true;
        $this->addSimpleReferencedColumns([
            '@disqualified_person.case_id',
            '@person.full_name',
            '@disqualified_person.begin',
            '@disqualified_person.end',
            '@disqualified_person.note',
        ]);
    }

    /**
     * @return TypedSelection<DisqualifiedPersonModel>
     */
    protected function getModels(): TypedSelection
    {
        $query = $this->disqualifiedPersonService->getTable();
        foreach ($this->filterParams as $key => $filterParam) {
            if (!$filterParam) {
                continue;
            }
            switch ($key) {
                case 'date':
                    $query->where('begin < ?', $this->filterParams['date']);
                    $query->where('end > ? OR end IS NULL', $this->filterParams['date']);
                    break;
            }
        }
        return $query;
    }

    protected function configureForm(Form $form): void
    {
        $form->addComponent(new DateInput(_('Active up to date')), 'date');
    }
}