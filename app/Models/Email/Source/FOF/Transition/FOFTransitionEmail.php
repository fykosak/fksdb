<?php

declare(strict_types=1);

namespace FKSDB\Models\Email\Source\FOF\Transition;

use FKSDB\Models\Email\TransitionEmailSource;
use FKSDB\Models\ORM\Models\AuthTokenModel;
use FKSDB\Models\ORM\Models\AuthTokenType;
use FKSDB\Models\ORM\Models\EmailMessageTopic;
use FKSDB\Models\ORM\Models\Fyziklani\TeamMemberModel;
use FKSDB\Models\ORM\Models\Fyziklani\TeamModel2;
use FKSDB\Models\ORM\Models\Fyziklani\TeamTeacherModel;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\ORM\Services\AuthTokenService;
use FKSDB\Models\ORM\Services\LoginService;
use FKSDB\Models\Transitions\Holder\TeamHolder;
use FKSDB\Models\Transitions\Transition\Transition;
use FKSDB\Modules\Core\Language;

/**
 * @phpstan-extends TransitionEmailSource<TeamModel2,array{token:AuthTokenModel,model:TeamModel2}>
 */
final class FOFTransitionEmail extends TransitionEmailSource
{
    protected AuthTokenService $authTokenService;
    protected LoginService $loginService;

    public function injectSecondary(
        AuthTokenService $authTokenService,
        LoginService $loginService
    ): void {
        $this->loginService = $loginService;
        $this->authTokenService = $authTokenService;
    }

    protected function createToken(PersonModel $person, TeamModel2 $teamModel): AuthTokenModel
    {
        return $this->authTokenService->createToken(
            $person->getLogin() ?? $this->loginService->createLogin($person),
            AuthTokenType::from(AuthTokenType::EVENT_NOTIFY),
            $teamModel->event->registration_end,
            null,
            true
        );
    }

    protected function getSource(array $params): array
    {
        /** @var TeamHolder $holder */
        $holder = $params['holder'];
        /** @phpstan-var  Transition<TeamHolder> $transition */
        $transition = $params['transition'];

        $emails = [];
        $transitionId = self::resolveLayoutName($transition);
        $lang = $holder->getModel()->game_lang->value;
        /** @var TeamMemberModel $member */
        foreach ($holder->getModel()->getMembers() as $member) {
            $emails[] = [
                'template' => [
                    'file' => __DIR__ . DIRECTORY_SEPARATOR . "member.$transitionId.$lang.latte",
                    'data' => [
                        'model' => $holder->getModel(),
                        'token' => $this->createToken($member->person, $holder->getModel()),
                    ],
                ],
                'data' => [
                    'recipient_person_id' => $member->person_id,
                    'sender' => 'Fyziklani <fyziklani@fykos.cz>',
                    'topic' => EmailMessageTopic::from(EmailMessageTopic::FOF),
                    'lang' => Language::from($lang),
                ],
            ];
        }
        /** @var TeamTeacherModel $teacher */
        foreach ($holder->getModel()->getTeachers() as $teacher) {
            $emails[] = [
                'template' => [
                    'file' => __DIR__ . DIRECTORY_SEPARATOR . "teacher.$transitionId.$lang.latte",
                    'data' => [
                        'model' => $holder->getModel(),
                        'token' => $this->createToken($teacher->person, $holder->getModel()),
                    ],
                ],
                'data' => [
                    'recipient_person_id' => $teacher->person_id,
                    'sender' => 'Fyziklani <fyziklani@fykos.cz>',
                    'topic' => EmailMessageTopic::from(EmailMessageTopic::FOF),
                    'lang' => Language::from($lang),
                ],
            ];
        }
        return $emails;
    }
}
