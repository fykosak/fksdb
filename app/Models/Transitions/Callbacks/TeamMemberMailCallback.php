<?php

declare(strict_types=1);

namespace FKSDB\Models\Transitions\Callbacks;

use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Models\AuthTokenModel;
use FKSDB\Models\ORM\Models\Fyziklani\TeamMemberModel;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\Transitions\Holder\FyziklaniTeamHolder;
use FKSDB\Models\Transitions\Holder\ModelHolder;

class TeamMemberMailCallback extends MailCallback
{
    protected function getPersonsFromHolder(ModelHolder $holder): array
    {
        if (!$holder instanceof FyziklaniTeamHolder) {
            throw new BadTypeException(FyziklaniTeamHolder::class, $holder);
        }
        $persons = [];
        /** @var TeamMemberModel $member */
        foreach ($holder->getModel()->getMembers() as $member) {
            $persons[] = $member->person;
        }
        return $persons;
    }

    /**
     * @throws BadTypeException
     */
    protected function createToken(PersonModel $person, ModelHolder $holder): AuthTokenModel
    {
        if (!$holder instanceof FyziklaniTeamHolder) {
            throw new BadTypeException(FyziklaniTeamHolder::class, $holder);
        }
        return $this->authTokenService->createToken(
            $this->resolveLogin($person),
            AuthTokenModel::TYPE_EVENT_NOTIFY,
            $holder->getModel()->event->registration_end,
            null,
            true
        );
    }

    /**
     * @throws BadTypeException
     */
    protected function getTemplatePath(ModelHolder $holder): string
    {
        if (!$holder instanceof FyziklaniTeamHolder) {
            throw new BadTypeException(FyziklaniTeamHolder::class, $holder);
        }
        switch ($holder->getModel()->event->event_type_id) {
            case 1:
                return 'fof/member';
            case 9:
                return 'fol/member';
        }
        throw new \InvalidArgumentException('Event is not supported');
    }

    /**
     * @throws BadTypeException
     */
    protected function getData(ModelHolder $holder): array
    {
        if (!$holder instanceof FyziklaniTeamHolder) {
            throw new BadTypeException(FyziklaniTeamHolder::class, $holder);
        }
        switch ($holder->getModel()->event->event_type_id) {
            case 1:
                return [
                    'subject' => _('Fyziklani Team Registration'),
                    'blind_carbon_copy' => 'FYKOS <fyziklani@fykos.cz>',
                    'sender' => 'Fyziklání <fyziklani@fykos.cz>',
                ];
            case 9:
                return [
                    'subject' => _('Physics Brawl Online Team Registration'),
                    'blind_carbon_copy' => 'Fyziklání Online <online@fyziklani.cz>',
                    'sender' => _('Physics Brawl Online <online@physicsbrawl.org>'),
                ];
        }
        throw new \InvalidArgumentException('Event is not supported');
    }
}
