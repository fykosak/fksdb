<?php

declare(strict_types=1);

namespace FKSDB\Components\Grids\Spam;

use FKSDB\Components\Grids\Components\BaseGrid;
use FKSDB\Models\ORM\Models\PersonHistoryModel;
use FKSDB\Models\ORM\Services\PersonHistoryService;
use Fykosak\NetteORM\Selection\TypedSelection;
use Fykosak\Utils\UI\Title;
use Nette\Forms\Form;

/**
 * @phpstan-extends BaseGrid<PersonHistoryModel,array{
 *      person_name?:string,
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
        /** @var string $key */
        foreach ($this->filterParams as $key => $filterParam) {
            if (!$filterParam) {
                continue;
            }
            switch ($key) {
                case 'person_name':
                    $tokens = explode(' ', $filterParam);
                    foreach ($tokens as $token) {
                        $query->where(
                            'CONCAT(person.other_name, person.family_name) LIKE CONCAT(\'%\', ? , \'%\')',
                            $token
                        );
                    }
                    break;
                case 'school':
                    $tokens = explode(' ', $filterParam);
                    foreach ($tokens as $token) {
                        $query->where('school.name_full LIKE CONCAT(\'%\', ? , \'%\')', $token);
                    }
                    break;
                case 'school_label':
                    $tokens = explode(' ', $filterParam);
                    foreach ($tokens as $token) {
                        $query->where('person_history.school_label_key LIKE CONCAT(\'%\', ? , \'%\')', $token);
                    }
                    break;
            }
        }

        return $query;
    }

    protected function configureForm(Form $form): void
    {
        $form->addText('person_name', _('Person name'))->setHtmlAttribute('placeholder', _('Person name'));
        $form->addText('school', _('School'))->setHtmlAttribute('placeholder', _('School'));
        $form->addText('school_label', _('School label'))->setHtmlAttribute('placeholder', _('School label'));
    }

    protected function configure(): void
    {
        $this->paginate = true;
        $this->counter = true;
        $this->filtered = true;
        $this->addSimpleReferencedColumns([
            '@person.other_name',
            '@person.family_name',
            '@person_history.school_label_key',
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
