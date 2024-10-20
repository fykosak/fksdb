<?php

declare(strict_types=1);

namespace FKSDB\Components\Applications\Single\InvitedForms;

use FKSDB\Components\Schedule\Input\SectionGroupOptions;
use FKSDB\Models\Events\Semantics\State;
use FKSDB\Models\Exceptions\NotImplementedException;
use FKSDB\Models\Expressions\Logic\LogicAnd;
use FKSDB\Models\Expressions\Logic\LogicOr;
use FKSDB\Models\Expressions\Logic\Not;
use FKSDB\Models\ORM\Models\Schedule\ScheduleGroupType;
use FKSDB\Models\Transitions\Holder\ParticipantHolder;
use FKSDB\Models\Transitions\Transition\Statements\Conditions\EventRole;

class SousForm extends InvitationApplicationForm
{
    /**
     * @throws NotImplementedException
     */
    protected function getPersonFieldsDefinition(): array
    {
        $holder = null;
        if ($this->model) {
            $machine = $this->eventDispatchFactory->getParticipantMachine($this->event);
            $holder = $machine->createHolder($this->model);
        }
        $reqCondition = fn(?ParticipantHolder $holder): bool => isset($holder) ?
            (new LogicOr(
                new LogicAnd(
                    new Not(
                        new EventRole('organizer', $this->container)
                    ),
                    new LogicOr(
                        new State('applied'),
                        new State('interested')
                    )
                ),
                new State('participated')
            ))($holder)
            : true;
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
                'born_id' => [
                    'required' => $reqCondition($holder),
                    'description' => _('For the insurance company.'),
                ],
                'birthplace' => [
                    'required' => isset($holder) ? (new State('participated'))($holder) : true,
                ],
                'phone' => [
                    'required' => $reqCondition($holder),
                    'description' => _('Telephone number (including state prefix), that you will carry with you.'),
                ],
            ],
            'post_contact_p' => [
                'address' => [
                    'required' => $reqCondition($holder),
                ],
            ],
            'person_history' => [
                'school_id' => [
                    'required' => isset($holder) ? (new State('participated'))($holder) : true,
                    'description' => _('If you cannot find your school, e-mail the webmaster.'),
                ],
            ],
            'person_schedule' => [
                'apparel' => [
                    'label' => _('T-shirt'),
                    'filter' => [
                        'types' => [
                            ScheduleGroupType::Apparel,
                        ],
                    ],
                    'collapseChild' => false,
                    'groupBy' => SectionGroupOptions::GroupNone,
                ],
            ],
        ];
    }

    protected function getParticipantFieldsDefinition(): array
    {
        return [
            'diet' => [
                'label' => _('Food'),
                'description' => _(
                    'Do you have any special diet – vegetarianism, veganism, ketogenic etc.?
                    If so, do you want us to get you the special diet or will you bring your own food?'
                ),
            ],
            'health_restrictions' => [
                'label' => _('Health restrictions'),
                'description' => _(
                    'Do you have any health restriction,
                    that could pose a problem for your participation or because of which you could not participate
                    in certain physically demanding (or night) activities? E.g. allergies, diabetes, epilepsy,
                    other chronic diseases... Do you take any medications,
                    be it periodically or in case of problems? Which?
                    Is there anything else about your health we should know about?'
                ),
            ],
        ];
    }
}
