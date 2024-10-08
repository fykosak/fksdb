<?php

declare(strict_types=1);

namespace FKSDB\Models\Email\Source\FOL;

use FKSDB\Models\Email\TransitionEmailSource;
use FKSDB\Models\ORM\Models\AuthTokenModel;
use FKSDB\Models\ORM\Models\AuthTokenType;
use FKSDB\Models\ORM\Models\EmailMessageTopic;
use FKSDB\Models\ORM\Models\Fyziklani\TeamMemberModel;
use FKSDB\Models\ORM\Models\Fyziklani\TeamModel2;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\ORM\Services\AuthTokenService;
use FKSDB\Models\ORM\Services\LoginService;
use FKSDB\Models\Transitions\Holder\TeamHolder;
use FKSDB\Modules\Core\Language;

/**
 * @phpstan-extends TransitionEmailSource<TeamModel2,array{token:AuthTokenModel,model:TeamModel2}>
 */
final class TeamMemberEmail extends TransitionEmailSource
{
    private AuthTokenService $authTokenService;
    private LoginService $loginService;

    public function injectSecondary(
        AuthTokenService $authTokenService,
        LoginService $loginService
    ): void {
        $this->loginService = $loginService;
        $this->authTokenService = $authTokenService;
    }

    /**
     * @throws \Throwable
     */
    protected function createToken(PersonModel $person, TeamHolder $holder): AuthTokenModel
    {
        return $this->authTokenService->createEventToken(
            $person->getLogin() ?? $this->loginService->createLogin($person),
            $holder->getModel()->event
        );
    }

    /**
     * @throws \Throwable
     */
    protected function getSource(array $params): array
    {
        /**
         * @var TeamHolder $holder
         */
        $holder = $params['holder'];
        $lang = Language::from($holder->getModel()->game_lang->value);
        $sender = 'Physics Brawl Online <online@physicsbrawl.org>';
        if ($lang == 'cs') {
            $sender = 'Fyziklání Online <online@fyziklani.cz>';
        }
        $emails = [];
        /** @var TeamMemberModel $member */
        foreach ($holder->getModel()->getMembers() as $member) {
            $emails[] = [
                'template' => [
                    'file' => __DIR__ . DIRECTORY_SEPARATOR . "member.$lang->value.latte",
                    'data' => [
                        'model' => $holder->getModel(),
                        'token' => $this->createToken($member->person, $holder),
                    ],
                ],
                'data' => [
                    'blind_carbon_copy' => 'Fyziklání Online <online@fyziklani.cz>',
                    'sender' => $sender,
                    'recipient_person_id' => $member->person_id,
                    'topic' => EmailMessageTopic::from(EmailMessageTopic::FOL),
                    'lang' => $lang,
                ],
            ];
        }
        return $emails;
    }
}
