<?php

declare(strict_types=1);

namespace FKSDB\Components\Applications\Single\OpenForms;

use FKSDB\Components\Forms\Containers\Models\ReferencedPersonContainer;
use FKSDB\Models\ORM\Models\Schedule\ScheduleGroupType;
use FKSDB\Modules\Core\BasePresenter;

/**
 * @method BasePresenter getPresenter($need = true)
 * @phpstan-import-type EvaluatedFieldsDefinition from ReferencedPersonContainer
 */
final class SetkaniForm extends OpenApplicationForm
{
    /**
     * @phpstan-return EvaluatedFieldsDefinition
     */
    protected function getPersonFieldsDefinition(): array
    {
        return [
            'person' => [
                'other_name' => ['required' => true],
                'family_name' => ['required' => true]
            ],
            'person_info' => [
                'email' => ['required' => true],
                'born' => ['required' => true],
                'id_number' => [
                    'required' => false,
                    'description' => _('Číslo OP/pasu, pokud máš')
                ],
                'phone_parent_m' => ['required' => false],
                'phone_parent_d' => ['required' => false],
                'phone' => ['required' => true]
            ],
            'person_schedule' => [
                'apparel' => [
                    'types' => [
                        ScheduleGroupType::from(ScheduleGroupType::Apparel),
                    ],
                    'required' => true,
                    'label' => _('Apparel'),
                ],
                'transport' => [
                    'types' => [
                        ScheduleGroupType::from(ScheduleGroupType::Transport),
                        ScheduleGroupType::from(ScheduleGroupType::Ticket),
                    ],
                    'required' => true,
                    'label' => _('Transport & Ticket'),
                ],
            ]
        ];
    }

    /**
     * @phpstan-return array<string, array<string, mixed>>
     */
    protected function getParticipantFieldsDefinition(): array
    {
        return [
            'diet' => [
                'required' => false,
                'description' => _('Máš nějaké speciální stravovací návyky – vegetariánství, veganství, diety, …?
                Pokud ano, máš zájem o speciální stravu nebo si (zejména v případě veganů) dovezeš jídlo vlastní?')
            ],
            'health_restrictions' => [
                'required' => false,
                'description' => _('Máš nějaká zdravotní omezení, která by tě mohla omezovat v pobytu na setkání?
                Například různé alergie (a jejich projevy), cukrovka, epilepsie, dlouhodobější obtíže, … Bereš
                nějaké léky, ať už pravidelně, nebo v případě obtíží? Jaké to jsou? Jsou nějaké další informace
                ohledně tvého zdravotního stavu, co bychom měli vědět?')],
            'note' => ['required' => false]
        ];
    }
}
