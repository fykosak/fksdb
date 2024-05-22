<?php

declare(strict_types=1);

namespace FKSDB\Components\Grids\Spam;

use FKSDB\Components\Grids\Components\BaseGrid;
use FKSDB\Models\ORM\DbNames;
use FKSDB\Models\ORM\Models\PersonHistoryModel;
use FKSDB\Models\ORM\Services\PersonHistoryService;
use Fykosak\NetteORM\Selection\TypedSelection;
use Fykosak\Utils\UI\Title;
use Nette\Forms\Form;

/**
 * @phpstan-extends BaseGrid<PersonHistoryModel,array{
 *      name?:string,
 *      school?:string
 * }>
 */
final class PersonGrid extends BaseGrid
{
    private PersonHistoryService $service;

    public function inject(PersonHistoryService $service): void
    {
        $this->service = $service;
    }

    /**
     * @phpstan-return TypedSelection<PersonHistoryModel>
     */
    protected function getModels(): TypedSelection
    {
        $query = $this->service->getTable()->order('person_history_id DESC');
        $query->joinWhere('person:person_has_flag', 'person:person_has_flag.ac_year = person_history.ac_year');
        $query->where('person:person_has_flag.flag.fid', 'source_spam');

        foreach ($this->filterParams as $key => $filterParam) {
            if (!$filterParam) {
                continue;
            }
            switch ($key) {
                case 'name':
                    $tokens = explode(' ', $filterParam);
                    foreach ($tokens as $token) {
                        $query->where('CONCAT(person.other_name, person.family_name) LIKE CONCAT(\'%\', ? , \'%\')', $token);
                    }
                    break;
                case 'school':
                    $tokens = explode(' ', $filterParam);
                    foreach ($tokens as $token) {
                        $query->where('school.name_full LIKE CONCAT(\'%\', ? , \'%\')', $token);
                    }
                    break;
            }
        }

        return $query;
    }

    protected function configureForm(Form $form): void
    {
        $form->addText('name', _('Name'))->setHtmlAttribute('placeholder', _('Name'));
        $form->addText('school', _('School'))->setHtmlAttribute('placeholder', _('School'));
    }

    protected function configure(): void
    {
        $this->paginate = true;
        $this->counter = true;
        $this->filtered = true;
        $this->addSimpleReferencedColumns([
            '@person.other_name',
            '@person.family_name',
            '@school_label.school_label_key',
            '@person_history.study_year_new',
            '@person_history.ac_year',
            '@school.school'
        ]);
        $this->addPresenterButton(
            'edit',
            'edit',
            new Title(null, _('button.edit')),
            false,
            ['id' => 'person_history_id']
        );
    }
}
