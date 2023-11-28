<?php

declare(strict_types=1);

namespace FKSDB\Models\Transitions\Callbacks\Fol;

use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Models\AuthTokenModel;
use FKSDB\Models\ORM\Models\AuthTokenType;
use FKSDB\Models\ORM\Models\Fyziklani\TeamMemberModel;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\Transitions\Callbacks\MailCallback;
use FKSDB\Models\Transitions\Holder\ModelHolder;
use FKSDB\Models\Transitions\Holder\TeamHolder;
use FKSDB\Models\Transitions\Transition\Transition;

/**
 * @phpstan-extends MailCallback<TeamHolder>
 */
class TeamMemberMailCallback extends MailCallback
{
    protected function getTemplatePath(ModelHolder $holder, Transition $transition): string
    {
        return __DIR__ . DIRECTORY_SEPARATOR . 'member';
    }

    /**
     * @phpstan-return array{
     *     blind_carbon_copy?:string,
     *     sender:string,
     * }
     */
    protected function getData(ModelHolder $holder): array
    {
        return [
            'blind_carbon_copy' => 'Fyziklání Online <online@fyziklani.cz>',
            'sender' => _('Physics Brawl Online <online@physicsbrawl.org>'),
        ];
    }

    /**
     * @param TeamHolder $holder
     * @throws BadTypeException
     */
    final protected function getPersons(ModelHolder $holder): array
    {
        if (!$holder instanceof TeamHolder) {
            throw new BadTypeException(TeamHolder::class, $holder);
        }
        $persons = [];
        /** @var TeamMemberModel $member */
        foreach ($holder->getModel()->getMembers() as $member) {
            $persons[] = $member->person;
        }
        return $persons;
    }

    /**
     * @param TeamHolder $holder
     * @throws BadTypeException
     */
    protected function createToken(PersonModel $person, ModelHolder $holder): AuthTokenModel
    {
        if (!$holder instanceof TeamHolder) {
            throw new BadTypeException(TeamHolder::class, $holder);
        }
        return $this->authTokenService->createToken(
            $this->resolveLogin($person),
            AuthTokenType::from(AuthTokenType::EVENT_NOTIFY),
            $holder->getModel()->event->registration_end,
            null,
            true
        );
    }
}
