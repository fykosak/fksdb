<?php

declare(strict_types=1);

namespace FKSDB\Components\EntityForms\Fyziklani;

use FKSDB\Components\EntityForms\Fyziklani\Processing\Category\FOLCategoryProcessing;
use FKSDB\Components\EntityForms\Fyziklani\Processing\FormProcessing;
use FKSDB\Components\EntityForms\Fyziklani\Processing\SchoolRequirement\FOLSchoolRequirementProcessing;
use FKSDB\Components\Forms\Containers\Models\ReferencedPersonContainer;
use FKSDB\Models\ORM\Columns\Tables\PersonHistory\StudyYearNewColumnFactory;

/**
 * @phpstan-import-type EvaluatedFieldMetaData from ReferencedPersonContainer
 * @phpstan-import-type EvaluatedFieldsDefinition from ReferencedPersonContainer
 */
class FOLTeamForm extends TeamForm
{
    /**
     * @phpstan-return FormProcessing[]
     */
    protected function getProcessing(): array
    {
        return [
            new FOLSchoolRequirementProcessing($this->container),
            new FOLCategoryProcessing($this->container),
        ];
    }

    public function render(): void
    {
        $this->template->event = $this->event;
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
                    'description' => _(
                        'Type a few characters of your school name, then select
                        your school from the list. If you cannot find it,
                        please send us email with your school details to
                        schola.novum@fykos.cz. We will add your school and send
                        you a reply. Then that you can proceed with
                        registration.'
                    ),
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

    /**
     * @phpstan-return array{}
     */
    protected function getTeacherFieldsDefinition(): array
    {
        return [];
    }
}
