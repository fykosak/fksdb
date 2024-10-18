<?php

declare(strict_types=1);

namespace FKSDB\Components\Applications\Team\Forms;

use FKSDB\Components\Applications\Team\Forms\Processing\Category\FOLCategoryProcessing;
use FKSDB\Components\Applications\Team\Forms\Processing\SchoolRequirement\FOLSchoolRequirementProcessing;
use FKSDB\Components\Forms\Containers\Models\ReferencedPersonContainer;
use FKSDB\Models\ORM\Columns\Tables\PersonHistory\StudyYearNewColumnFactory;
use FKSDB\Modules\Core\Language;
use Fykosak\Utils\Localization\LocalizedString;

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
                    'reason' => new LocalizedString([
                        'en' => 'Required for communication',
                        'cs' => 'Vyžadováno kvůli komunikaci',
                    ])
                ],
                'born' => [
                    'required' => true,
                    'reason' => new LocalizedString([
                        'en' => 'Required for certificates',
                        'cs' => 'Vyžadováno kvůli certifikátům',
                    ])
                ],
            ],
            'person_history' => [
                'school_id' => [
                    'required' => false,
                ],
                'study_year_new' => [
                    'required' => true,
                    'flag' => StudyYearNewColumnFactory::FLAG_ALL,
                    'reason' => new LocalizedString([
                        'en' => 'Required for category calculation',
                        'cs' => 'Vyžadováno kvůli kategorii',
                    ])
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
