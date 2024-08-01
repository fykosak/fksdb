<?php

declare(strict_types=1);

namespace FKSDB\Models\Email\Source\Tabor;

use FKSDB\Models\Email\TransitionEmailSource;
use FKSDB\Models\ORM\Models\EventParticipantModel;
use FKSDB\Models\Transitions\Holder\ParticipantHolder;
use FKSDB\Modules\Core\Language;

/**
 * @phpstan-extends TransitionEmailSource<EventParticipantModel,array{model:EventParticipantModel}>
 */
class OrganizerEmail extends TransitionEmailSource
{
    protected function getSource(array $params): array
    {
        /** @var ParticipantHolder $holder */
        $holder = $params['holder'];
        return [
            [
                'template' => [
                    'file' => __DIR__ . DIRECTORY_SEPARATOR . 'organizer.latte',
                    'data' => [
                        'model' => $holder->getModel(),
                    ],
                ],
                'lang' => Language::from(Language::CS),
                'data' => [
                    'sender' => 'Výfuk <vyfuk@vyfuk.org>',
                    'recipient' => 'Výfučí přihlášky <vyfuk-prihlasky@vyfuk.org>',
                ]
            ]
        ];
    }
}
