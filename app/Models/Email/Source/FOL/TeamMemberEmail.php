<?php

declare(strict_types=1);

namespace FKSDB\Models\Email\Source\FOL;

use FKSDB\Models\Email\TransitionEmailSource;
use FKSDB\Models\ORM\Models\AuthTokenModel;
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
        $emails = [];
        /** @var TeamMemberModel $member */
        foreach ($holder->getModel()->getMembers() as $member) {
            $emails[] = [
                'template' => [
                    'file' => __DIR__ . DIRECTORY_SEPARATOR . "member.$lang.latte",
                    'data' => [
                        'model' => $holder->getModel(),
                        'token' => $this->createToken($member->person, $holder),
                    ],
                ],
                'lang' => $lang,
                'data' => [
                    'blind_carbon_copy' => 'Fyziklání Online <online@fyziklani.cz>',
                    'sender' => _('Physics Brawl Online <online@physicsbrawl.org>'),
                    'recipient_person_id' => $member->person_id,
                ],
            ];
        }
        return $emails;
    }
}
