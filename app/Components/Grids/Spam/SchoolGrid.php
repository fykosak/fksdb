<?php

declare(strict_types=1);

namespace FKSDB\Components\Grids\Spam;

use FKSDB\Components\Grids\Components\BaseGrid;
use FKSDB\Models\ORM\Models\Spam\SpamSchoolModel;
use FKSDB\Models\ORM\Services\Spam\SpamSchoolService;
use Fykosak\NetteORM\Selection\TypedSelection;
use Fykosak\Utils\UI\Title;
use Nette\Forms\Form;

/**
 * @phpstan-extends BaseGrid<SpamSchoolModel,array{
 *     name?:string,
 *     not_set?:bool
 * }>
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
        $query = $this->service->getTable();

        if (isset($this->filterParams['name'])) {
            $tokens = explode(' ', $this->filterParams['name']);
            foreach ($tokens as $token) {
                $query->where('school.name_full LIKE CONCAT(\'%\', ? , \'%\')', $token);
            }
        }

        if (isset($this->filterParams['not_set']) && $this->filterParams['not_set']) {
            $query->where('school_id', null);
        }

        return $query;
    }

    protected function configureForm(Form $form): void
    {
        $form->addText('name', _('Name'))->setHtmlAttribute('placeholder', _('Name'));
        $form->addCheckbox('not_set', _('Not set'));
    }

    protected function configure(): void
    {
        $this->paginate = true;
        $this->counter = true;
        $this->filtered = true;
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
