<?php

declare(strict_types=1);

namespace FKSDB\Components\Applications\Team\Forms;

use FKSDB\Components\Applications\Team\Forms\Processing\Category\FOLCategoryProcessing;
use FKSDB\Components\Applications\Team\Forms\Processing\SchoolRequirement\FOLSchoolRequirementProcessing;
use FKSDB\Components\Forms\Containers\Models\ReferencedPersonContainer;
use FKSDB\Models\ORM\Columns\Tables\PersonHistory\StudyYearNewColumnFactory;
use FKSDB\Modules\Core\Language;

/**
 * @phpstan-import-type EvaluatedFieldMetaData from ReferencedPersonContainer
 * @phpstan-import-type EvaluatedFieldsDefinition from ReferencedPersonContainer
 */
class FOLTeamForm extends TeamForm
{
    protected function getPreprocessing(): array
    {
        return [
            new FOLSchoolRequirementProcessing($this->container, $this->event),
            new FOLCategoryProcessing($this->container, $this->event),
        ];
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
                ],
                'born' => [
                    'required' => true,
                    'description' => _('For certificates'),
                ],
            ],
            'person_history' => [
                'school_id' => [
                    'required' => false,
                ],
                'study_year_new' => [
                    'required' => true,
                    'description' => _('For category calculation.'),
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
            'game_lang' => ['required' => true, 'caption' => _('Language of communication')],
        ];
    }
}
