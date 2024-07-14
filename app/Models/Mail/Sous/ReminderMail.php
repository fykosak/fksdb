<?php

declare(strict_types=1);

namespace FKSDB\Models\Mail\Sous;

use FKSDB\Models\Email\Source\MailSource;
use FKSDB\Models\ORM\Models\EventParticipantModel;
use FKSDB\Models\ORM\Models\EventParticipantStatus;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\ORM\Services\EventParticipantService;
use FKSDB\Modules\Core\Language;
use Fykosak\NetteORM\Selection\TypedGroupedSelection;

/**
 * @phpstan-extends MailSource<EventParticipantModel,array{person:PersonModel,model:EventParticipantModel,token:null},array{event_id:int}>
 */
abstract class ReminderMail extends MailSource
{
    protected EventParticipantService $eventParticipantService;

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

    /**
     * @phpstan-return  TypedGroupedSelection<EventParticipantModel>
     */
    protected function getSource(array $params): TypedGroupedSelection
    {
        /** @phpstan-var TypedGroupedSelection<EventParticipantModel> $source */
        $source = $this->eventParticipantService->getTable()
            ->where('event_id', $params['event_id'])
            ->where('status', [EventParticipantStatus::INVITED, EventParticipantStatus::SPARE]);
        return $source;
    }

    protected function getTemplateParams($source): array
    {
        return [
            'person' => $source->person,
            'model' => $source,
            'token' => null,
        ];
    }

    protected function getEmailData($source): array
    {
        return [
            'recipient_person_id' => $source->person_id,
            'blind_carbon_copy' => 'Soustředění FYKOSu <soustredeni@fykos.cz>',
            'sender' => 'Soustředění FYKOSu <soustredeni@fykos.cz>',
        ];
    }

    protected function getEmailLang($source): Language
    {
        return Language::from(Language::CS);
    }
}
