<?php

declare(strict_types=1);

namespace FKSDB\Models\Mail\FOF;

use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Models\AuthTokenModel;
use FKSDB\Models\ORM\Models\AuthTokenType;
use FKSDB\Models\ORM\Models\Fyziklani\TeamMemberModel;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\Transitions\Callbacks\MailCallback;
use FKSDB\Models\Transitions\Holder\ModelHolder;
use FKSDB\Models\Transitions\Holder\TeamHolder;
use FKSDB\Models\Transitions\Transition\Transition;
use Nette\InvalidStateException;

/**
 * @phpstan-extends MailCallback<TeamHolder>
 */
class MemberTransitionMail extends MailCallback
{
    /**
     * @param TeamHolder $holder
     * @phpstan-param Transition<TeamHolder> $transition
     */
    protected function getTemplatePath(ModelHolder $holder, Transition $transition): string
    {
        return __DIR__ . DIRECTORY_SEPARATOR . 'member.' . self::resolveLayoutName($transition);
    }

    /**
     * @param TeamHolder $holder
     * @phpstan-param Transition<TeamHolder> $transition
     * @phpstan-return array{
     *     blind_carbon_copy:string|null,
     *     subject:string,
     *     sender:string,
     * }
     */
    protected function getData(ModelHolder $holder, Transition $transition): array
    {
        if ($holder->getModel()->game_lang->value === 'cs') {
            switch (self::resolveLayoutName($transition)) {
                case 'init->pending':
                    $subject = 'Registrace na Fyziklání – ' . $holder->getModel()->name;
                    break;
                case 'pending->spare':
                    $subject = 'Změna stavu - ' . $holder->getModel()->name;
                    break;
                case 'spare->applied':
                    $subject = 'Změna stavu - ' . $holder->getModel()->name;
                    break;
                default:
                    throw new InvalidStateException();
            }
            $sender = 'Fyziklání <fyziklani@fykos.cz>';
        } else {
            switch (self::resolveLayoutName($transition)) {
                case 'init->pending':
                    $subject = 'Fyziklani Registration – ' . $holder->getModel()->name;
                    break;
                case 'pending->spare':
                    $subject = '';
                    break;
                case 'spare->applied':
                    $subject = '' . $holder->getModel()->name;
                    break;
                default:
                    throw new InvalidStateException();
            }
            $sender = 'Fyziklani <fyziklani@fykos.cz>';
        }
        return [
            'subject' => $subject,
            'blind_carbon_copy' => 'FYKOS <fyziklani@fykos.cz>',
            'sender' => $sender,
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
