<?php

declare(strict_types=1);

namespace FKSDB\Models\Email\Source\Setkani;

use FKSDB\Models\Email\EmailSource;
use FKSDB\Models\ORM\Models\EmailMessageTopic;
use FKSDB\Models\ORM\Models\EventParticipantModel;
use FKSDB\Models\Transitions\Holder\ParticipantHolder;
use FKSDB\Modules\Core\Language;

/**
 * @phpstan-extends EmailSource<array{model:EventParticipantModel},array{holder:ParticipantHolder}>
 */
class OrganizerTransitionEmail extends EmailSource
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
                'data' => [
                    'sender' => 'Výfuk <vyfuk@vyfuk.org>',
                    'recipient' => 'Výfučí přihlášky <vyfuk-prihlasky@vyfuk.org>',
                    'topic' => EmailMessageTopic::from(EmailMessageTopic::Contest),
                    'lang' => Language::from(Language::CS),
                ]
            ]
        ];
    }
}
