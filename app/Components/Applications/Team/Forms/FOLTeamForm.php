<?php

declare(strict_types=1);

namespace FKSDB\Components\Applications\Team\Forms;

use FKSDB\Components\Applications\Team\Forms\Processing\Category\FOLCategoryProcessing;
use FKSDB\Components\Applications\Team\Forms\Processing\SchoolRequirement\FOLSchoolRequirementProcessing;
use FKSDB\Components\Forms\Containers\Models\ReferencedPersonContainer;
use FKSDB\Models\ORM\Columns\Tables\PersonHistory\StudyYearNewColumnFactory;
use FKSDB\Models\ORM\Models\Fyziklani\TeamModel2;
use FKSDB\Models\ORM\Models\Fyziklani\TeamState;
use FKSDB\Modules\Core\Language;
use Fykosak\NetteORM\Model\Model;

/**
 * @phpstan-import-type EvaluatedFieldMetaData from ReferencedPersonContainer
 * @phpstan-import-type EvaluatedFieldsDefinition from ReferencedPersonContainer
 */
class FOLTeamForm extends TeamForm
{
    protected function getPreprocessing(): array
    {
        return [
            ...parent::getPreprocessing(),
            new FOLSchoolRequirementProcessing($this->container, $this->event),
            new FOLCategoryProcessing($this->container, $this->event),
        ];
    }

    protected function getPostprocessing(): array
    {
        $processing = parent::getPostprocessing();
        $processing[] = function (TeamModel2 $model): void {
            if ($model->state->value !== TeamState::Pending) {
                $holder = $this->machine->createHolder($model);
                // nieje čakajúci a nieje to editáci orga pošle to do čakajucich
                $transition = $this->machine->getTransitions()
                    ->filterByTarget(TeamState::from(TeamState::Pending))
                    ->filterAvailable($holder)
                    ->select();
                $transition->execute($holder);
            }
        };
        return $processing;
    }

    public function render(): void
    {
        $this->template->event = $this->event;
        $this->template->lang = Language::tryFrom($this->translator->lang);
        parent::render();
    }

    protected function getTemplatePath(): string
    {
        return __DIR__ . DIRECTORY_SEPARATOR . 'layout.fol.latte';
    }

    /**
     * @phpstan-return EvaluatedFieldsDefinition
     */
    protected function getMemberFieldsDefinition(): array
    {
        return [
            'person' => [
                'other_name' => [
                    'required' => true,
                ],
                'family_name' => [
                    'required' => true,
                ],
            ],
            'person_info' => [
                'email' => [
                    'required' => true,
                    'description' => _('Required for communication'),
                ],
                'born' => [
                    'required' => true,
                    'description' => _('Required for certificates'),
                ],
            ],
            'person_history' => [
                'school_id' => [
                    'required' => false,
                ],
                'study_year_new' => [
                    'required' => true,
                    'description' => _('Required for category calculation'),
                    'flag' => StudyYearNewColumnFactory::FLAG_ALL,
                ],
            ],
        ];
    }

    /**
     * @phpstan-return array<string,EvaluatedFieldMetaData>
     */
    protected function getTeamFieldsDefinition(): array
    {
        return [
            'name' => ['required' => true],
            'game_lang' => [
                'required' => true,
                'caption' => _('Language of communication'),
                'description' => _('Preferred language for communication.
                    The game language can be freely changed during the competition.')
            ],
        ];
    }
}
