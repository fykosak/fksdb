<?php

declare(strict_types=1);

namespace FKSDB\Models\Mail\FOF;

use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Models\AuthTokenModel;
use FKSDB\Models\ORM\Models\AuthTokenType;
use FKSDB\Models\ORM\Models\EmailMessageTopic;
use FKSDB\Models\ORM\Models\Fyziklani\GameLang;
use FKSDB\Models\ORM\Models\Fyziklani\TeamMemberModel;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\Transitions\Callbacks\MailCallback;
use FKSDB\Models\Transitions\Holder\ModelHolder;
use FKSDB\Models\Transitions\Holder\TeamHolder;
use FKSDB\Models\Transitions\Transition\Transition;
use FKSDB\Modules\Core\Language;
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
        $transitionId = self::resolveLayoutName($transition);
        $lang = $holder->getModel()->game_lang->value;
        return __DIR__ . DIRECTORY_SEPARATOR . "member.$transitionId.$lang.latte";
    }

    /**
     * @param TeamHolder $holder
     */
    protected function getData(ModelHolder $holder): array
    {
        return self::getStaticData($holder);
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

    /**
     * @phpstan-return array{
     *     sender:string,
     *     lang:Language,
     *     topic:EmailMessageTopic,
     * }
     */
    public static function getStaticData(TeamHolder $holder): array
    {
        switch ($holder->getModel()->game_lang->value) {
            case GameLang::CS:
                $sender = 'Fyziklání <fyziklani@fykos.cz>';
                break;
            case GameLang::EN:
                $sender = 'Fyziklani <fyziklani@fykos.cz>';
                break;
            default:
                throw new InvalidStateException();
        }
        return [
            'topic' => EmailMessageTopic::from(EmailMessageTopic::FOF),
            'lang' => Language::from($holder->getModel()->game_lang->value),
            'sender' => $sender,
        ];
    }
}
