<?php

declare(strict_types=1);

namespace FKSDB\Models\Email\Sous;

use FKSDB\Models\Mail\MailSource;
use FKSDB\Models\ORM\Models\EmailMessageTopic;
use FKSDB\Models\ORM\Models\EventParticipantModel;
use FKSDB\Models\ORM\Models\EventParticipantStatus;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\ORM\Services\EventParticipantService;
use FKSDB\Modules\Core\Language;

/**
 * @phpstan-extends MailSource<array{person:PersonModel,model:EventParticipantModel,token:null},array{event_id:int}>
 */
abstract class ReminderMail extends MailSource
{
    protected EventParticipantService $eventParticipantService;

    abstract protected function getTemplatePath(): string;

    public function getExpectedParams(): array
    {
        return [
            'event_id' => 'int',
        ];
    }

    public function injectService(EventParticipantService $eventParticipantService): void
    {
        $this->eventParticipantService = $eventParticipantService;
    }

    protected function getSource(array $params): array
    {
        $source = $this->eventParticipantService->getTable()
            ->where('event_id', $params['event_id'])
            ->where('status', [EventParticipantStatus::INVITED, EventParticipantStatus::SPARE]);
        $data = [];
        /** @var EventParticipantModel $participant */
        foreach ($source as $participant) {
            $data[] = [
                'template' => [
                    'file' => $this->getTemplatePath(),
                    'data' => [
                        'person' => $participant->person,
                        'model' => $participant,
                        'token' => null,
                    ],
                ],
                'lang' => Language::from(Language::CS),
                'data' => [
                    'recipient_person_id' => $participant->person_id,
                    'blind_carbon_copy' => 'Soustředění FYKOSu <soustredeni@fykos.cz>',
                    'sender' => 'Soustředění FYKOSu <soustredeni@fykos.cz>',
                    'topic' => EmailMessageTopic::from(EmailMessageTopic::Contest),
                    'lang' => Language::from(Language::CS),
                ],
            ];
        }
        return $data;
    }
}
