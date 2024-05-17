<?php

declare(strict_types=1);

namespace FKSDB\Components\Grids\Spam;

use FKSDB\Components\Grids\Components\BaseGrid;
use FKSDB\Models\ORM\Models\Spam\SpamPersonModel;
use FKSDB\Models\ORM\Services\Spam\SpamPersonService;
use Fykosak\NetteORM\Selection\TypedSelection;
use Fykosak\Utils\UI\Title;
use Nette\Forms\Form;

/**
 * @phpstan-extends BaseGrid<SpamPersonModel,array{
 *      name?:string,
 *      school?:string
 * }>
 */
final class PersonGrid extends BaseGrid
{
    private SpamPersonService $service;

    public function inject(SpamPersonService $service): void
    {
        $this->service = $service;
    }

    /**
     * @phpstan-return TypedSelection<SpamPersonModel>
     */
    protected function getModels(): TypedSelection
    {
        $query = $this->service->getTable()->order('spam_person_id DESC');
        foreach ($this->filterParams as $key => $filterParam) {
            if (!$filterParam) {
                continue;
            }
            switch ($key) {
                case 'name':
                    $tokens = explode(' ', $filterParam);
                    foreach ($tokens as $token) {
                        $query->where('CONCAT(other_name, family_name) LIKE CONCAT(\'%\', ? , \'%\')', $token);
                    }
                    break;
                case 'school':
                    $tokens = explode(' ', $filterParam);
                    foreach ($tokens as $token) {
                        $query->where('spam_school.school.name_full LIKE CONCAT(\'%\', ? , \'%\')', $token);
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
            '@spam_person.spam_person_id',
            '@spam_person.other_name',
            '@spam_person.family_name',
            '@spam_person.spam_school_label',
            '@spam_person.study_year_new',
            '@spam_person.ac_year',
            '@school.school',
        ]);
        $this->addPresenterButton(
            'edit',
            'edit',
            new Title(null, _('button.edit')),
            false,
            ['id' => 'spam_person_id']
        );
    }
}
