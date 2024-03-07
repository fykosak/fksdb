<?php

declare(strict_types=1);

namespace FKSDB\Components\Controls\Events;

use FKSDB\Components\Schedule\Input\ScheduleContainer;

class SousApplicationForm extends ApplicationComponent
{
    protected function getPersonFieldsDefinition(): array
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
                'born_id' => [
                    'required' => true,// '%events.soustredeni.requiredCond%',
                    'description' => _("For the insurance company."),
                ],
                'birthplace' => [
                    'required' => true //FKSDB\Models\Events\Semantics\State('participated'),
                ],
                'phone' => [
                    'required' => true,//'%events.soustredeni.requiredCond%',
                    'description' => _("Telephone number (including state prefix), that you will carry with you."),
                ],
            ],
            'post_contact_p' => [
                'address' => [
                    'required' => true,//'%events.soustredeni.requiredCond%',
                ],
            ],
            'person_history' => [
                'school_id' => [
                    'required' => true,// FKSDB\Models\Events\Semantics\State('participated'),
                    'description' => _('If you cannot find your school, e-mail the webmaster.'),
                ],
            ],
            'person_schedule' => [
                'apparel' => [
                    'label' => _('T-shirt'),
                    'types' => ['apparel'],
                    'collapseChild' => false,
                    'groupBy' => ScheduleContainer::GROUP_NONE,
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
                    'Do you have any special diet – vegetarianism, veganism, ketogenic etc.? If so, do you want us to get you the special diet or will you bring your own food?'
                ),
            ],
            'health_restrictions' => [
                'label' => _('Health restrictions'),
                'description' => _(
                    'Do you have any health restriction, that could pose a problem for your participation or because of which you could not participate in certain physicaly demanding (or night) activities? E.g. alergies, diabetes, epilepsy, other chronic diseases... Do you taky any medications, be it periodically or in case of problems? Which? Is there anything else about your health we should know about?'
                ),
            ],
        ];
    }

    protected function getFormAdjustment(): array
    {
        return [];
    }

    protected function getProcessing(): array
    {
        return [];
    }
}
