<?php

declare(strict_types=1);

namespace FKSDB\Components\Grids;

use FKSDB\Components\Forms\Controls\DateInputs\DateInput;
use FKSDB\Components\Grids\Components\BaseList;
use FKSDB\Components\Grids\Components\Container\RowContainer;
use FKSDB\Components\Grids\Components\Referenced\SimpleItem;
use FKSDB\Components\Grids\Components\Referenced\TemplateItem;
use FKSDB\Components\Grids\Components\Table\RelatedTable;
use FKSDB\Models\ORM\Models\BannedPersonModel;
use FKSDB\Models\ORM\Models\BannedPersonScopeModel;
use FKSDB\Models\ORM\Services\BannedPersonService;
use Fykosak\NetteORM\Selection\TypedGroupedSelection;
use Fykosak\NetteORM\Selection\TypedSelection;
use Fykosak\Utils\UI\Title;
use Nette\Forms\Form;
use Nette\Utils\DateTime;

/**
 * @phpstan-extends BaseList<BannedPersonModel,array{date?:DateTime|null}>
 */
final class BannedPersonGrid extends BaseList
{
    private BannedPersonService $bannedPersonService;

    public function injectService(BannedPersonService $bannedPersonService): void
    {
        $this->bannedPersonService = $bannedPersonService;
    }

    protected function configure(): void
    {
        $this->filtered = true;
        $this->mode = self::ModePanel;
        $this->setTitle(new SimpleItem($this->container, '@person.full_name'));
        /** @phpstan-var RowContainer<BannedPersonModel> $row */
        $row = new RowContainer($this->container);
        $row->addComponent(new TemplateItem($this->container, '<b>case ID:</b> @banned_person.case_id'), 'case_id');
        $row->addComponent(new SimpleItem($this->container, '<b>note:</b> @banned_person.note'), 'note');

        $this->addRow($row, 'row');
        /** @phpstan-var RelatedTable<BannedPersonModel,BannedPersonScopeModel> $table */
        $table = new RelatedTable(
            $this->container,
            /** @phpstan-return TypedGroupedSelection<BannedPersonScopeModel> */
            fn(BannedPersonModel $model): TypedGroupedSelection => $model->getScopes(),
            new Title(null, _('Scopes')),
            true
        );
        $this->addRow($table, 'scope');
        $table->addTableColumn(new SimpleItem($this->container, '@banned_person_scope.begin'), 'begin');
        $table->addTableColumn(new SimpleItem($this->container, '@banned_person_scope.end'), 'end');
        $table->addTableColumn(new SimpleItem($this->container, '@event_type.name'), 'event');
        $table->addTableColumn(new SimpleItem($this->container, '@contest.contest'), 'contest');
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
                    $query->where(':banned_person_scope.begin < ?', $this->filterParams['date']);
                    $query->where(':banned_person_scope.end > ? OR :banned_person_scope.end IS NULL', $this->filterParams['date']);
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
