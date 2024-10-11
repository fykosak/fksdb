<?php

declare(strict_types=1);

namespace FKSDB\Components\Grids;

use FKSDB\Components\Grids\Components\BaseGrid;
use FKSDB\Models\ORM\Models\SchoolLabelModel;
use FKSDB\Models\ORM\Services\SchoolLabelService;
use Fykosak\NetteORM\Selection\TypedSelection;
use Fykosak\Utils\UI\Title;
use Nette\Forms\Form;

/**
 * @phpstan-extends BaseGrid<SchoolLabelModel,array{
 *     school_name?:string,
 *     school_label?:string,
 *     not_set?:bool
 * }>
 */
final class SchoolLabelGrid extends BaseGrid
{
    private SchoolLabelService $service;

    public function inject(SchoolLabelService $service): void
    {
        $this->service = $service;
    }

    /**
     * @phpstan-return TypedSelection<SchoolLabelModel>
     */
    protected function getModels(): TypedSelection
    {
        $query = $this->service->getTable();

        foreach ($this->filterParams as $key => $filterParam) {
            if (!$filterParam) {
                continue;
            }
            switch ($key) {
                case 'school_name':
                    $tokens = explode(' ', $this->filterParams['school_name']);
                    foreach ($tokens as $token) {
                        $query->where(
                            'CONCAT(school.name_full, school.name_abbrev, school.address.city)
                            LIKE CONCAT(\'%\', ? , \'%\')',
                            $token
                        );
                    }
                    break;
                case 'school_label':
                    $tokens = explode(' ', $filterParam);
                    foreach ($tokens as $token) {
                        $query->where('school_label_key LIKE CONCAT(\'%\', ? , \'%\')', $token);
                    }
                    break;
                case 'not_set':
                    $query->where('school_id', null);
                    break;
            }
        }

        return $query;
    }

    protected function configureForm(Form $form): void
    {
        $form->addText('school_name', _('School name'))->setHtmlAttribute('placeholder', _('School name'));
        $form->addText('school_label', _('School label'))->setHtmlAttribute('placeholder', _('School label'));
        $form->addCheckbox('not_set', _('Not set'));
    }

    protected function configure(): void
    {
        $this->paginate = true;
        $this->counter = true;
        $this->filtered = true;
        $this->addSimpleReferencedColumns([
            '@school_label.school_label_id',
            '@school_label.school_label_key',
            '@school.school',
        ]);
        $this->addPresenterButton(
            'edit',
            'edit',
            new Title(null, _('button.edit')),
            false,
            ['id' => 'school_label_id']
        );
    }
}
