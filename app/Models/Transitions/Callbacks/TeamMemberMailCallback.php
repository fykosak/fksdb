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

    protected function getData(PersonModel $person, ModelHolder $holder): array
    {
        if (!$holder instanceof FyziklaniTeamHolder) {
            throw new BadTypeException(FyziklaniTeamHolder::class, $holder);
        }
        switch($holder->getModel()->event->event_type_id){
            case 1:
                $subject = _('Fyziklani Team Registration');
                return [
                    'subject' => $subject,
                    'blind_carbon_copy' => 'FYKOS <fyziklani@fykos.cz>',
                    'sender' => 'Fyziklání <fyziklani@fykos.cz>',
                ];
            case 9:
                $subject = _('Physics Brawl Online Team Registration');
                $sender = _('Physics Brawl Online <online@physicsbrawl.org>');
                return [
                    'subject' => $subject,
                    'blind_carbon_copy' => 'Fyziklání Online <online@fyziklani.cz>',
                    'sender' => $sender,
                ];
            default:
                return [
                    'subject' => $holder->getModel()->event->name,
                    'blind_carbon_copy' => 'FYKOS <fykos@fykos.cz>',
                    'sender' => 'FYKOS <fykos@fykos.cz>',
                ];
        }
    }
}
