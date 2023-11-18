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
                    'description' => _(
                        'Usually the first part of your name. For example, "Albert".'
                    ),
                ],
                'family_name' => [
                    'required' => true,
                    'description' => _(
                        'The second part of your name. For example, "Einstein".'
                    ),
                ],
            ],
            'person_info' => [
                'email' => [
                    'required' => true,
                ],
                'born' => [
                    'required' => false,
                    'description' => _('Only for Czech and Slovak students'),
                ],
            ],
            'person_history' => [
                'school_id' => [
                    'required' => false,
                    'description' => _(
                        'Napište prvních několik znaků vaší školy, školu pak vyberete ze seznamu. 
                        Pokud nelze školu nalézt, pošlete na email schola.novum@fykos.cz údaje o vaší škole jako název,
                        adresu a pokud možno i odkaz na webovou stránku.
                        Školu založíme a pošleme vám odpověď. Pak budete schopni dokončit 
                        registraci.'
                    ),
                ],
                'study_year_new' => [
                    'required' => true,
                    'description' => _('Pro výpočet kategorie.'),
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
        return ['name' => ['required' => true]];
    }

    /**
     * @phpstan-return array{}
     */
    protected function getTeacherFieldsDefinition(): array
    {
        return [];
    }
}
