<?php

declare(strict_types=1);

namespace FKSDB\Components\EntityForms\Single;

use FKSDB\Components\Forms\Containers\Models\ReferencedPersonContainer;
use FKSDB\Models\ORM\Models\Schedule\ScheduleGroupType;
use FKSDB\Models\Transitions\Transition\Transition;
use FKSDB\Modules\Core\BasePresenter;
use Nette\Forms\Controls\SubmitButton;
use Nette\Forms\Form;

/**
 * @method BasePresenter getPresenter($need = true)
 * @phpstan-import-type EvaluatedFieldsDefinition from ReferencedPersonContainer
 */
final class TaborFormComponent extends SingleFormComponent
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
                'born_id' => ['required' => true],
                'birthplace' => [
                    'required' => true,
                    'description' => _('Město a okres')
                ],
                'id_number' => [
                    'required' => false,
                    'description' => _('Číslo OP/pasu, pokud máš')
                ],
                'phone' => [
                    'required' => true,
                    'description' => _('Telefon (i s předvolbou státu), jenž budeš mít s sebou.')
                ],
                'phone_parent_m' => ['required' => true],
                'phone_parent_d' => ['required' => true],
            ],
            'person_history' => [
                'school_id' => ['required' => true]
            ],
            'person_schedule' => [
                'apparel' => [
                    'types' => [ScheduleGroupType::Apparel],
                    'required' => true,
                    'label' => _('Apparel'),
                ],
                'transport' => [
                    'types' => [ScheduleGroupType::Transport, ScheduleGroupType::Ticket],
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
                ohledně tvého zdravotního stavu, co bychom měli vědět?')
            ],
            'used_drugs' => ['required' => false],
            'swimmer' => ['required' => false],
            'note' => ['required' => false]
        ];
    }
}
