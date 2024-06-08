<?php

declare(strict_types=1);

namespace FKSDB\Components\Grids;

use FKSDB\Components\Forms\Controls\DateInputs\DateInput;
use FKSDB\Components\Grids\Components\BaseGrid;
use FKSDB\Models\ORM\Models\BannedPersonModel;
use FKSDB\Models\ORM\Services\BannedPersonService;
use Fykosak\NetteORM\Selection\TypedSelection;
use Nette\Forms\Form;
use Nette\Utils\DateTime;

/**
 * @phpstan-extends BaseGrid<BannedPersonModel,array{date?:DateTime|null}>
 */
final class BannedPersonGrid extends BaseGrid
{
    private BannedPersonService $bannedPersonService;

    public function injectService(BannedPersonService $bannedPersonService): void
    {
        $this->bannedPersonService = $bannedPersonService;
    }

    protected function configure(): void
    {
        $this->filtered = true;
        $this->addSimpleReferencedColumns([
            '@banned_person.case_id',
            '@person.full_name',
            '@banned_person.begin',
            '@banned_person.end',
            '@banned_person.note',
        ]);
    }

    /**
     * @return TypedSelection<BannedPersonModel>
     */
    protected function getModels(): TypedSelection
    {
        $query = $this->bannedPersonService->getTable();
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
