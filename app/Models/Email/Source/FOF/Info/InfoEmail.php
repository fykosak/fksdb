<?php

declare(strict_types=1);

namespace FKSDB\Models\Email\Source\FOF\Info;

use FKSDB\Models\Email\Source\EmailSource;
use FKSDB\Models\ORM\Models\AuthTokenModel;
use FKSDB\Models\ORM\Models\AuthTokenType;
use FKSDB\Models\ORM\Models\Fyziklani\TeamMemberModel;
use FKSDB\Models\ORM\Models\Fyziklani\TeamModel2;
use FKSDB\Models\ORM\Models\Fyziklani\TeamTeacherModel;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\ORM\Services\AuthTokenService;
use FKSDB\Models\ORM\Services\LoginService;
use FKSDB\Models\Transitions\Holder\TeamHolder;
use FKSDB\Modules\Core\Language;

/**
 * @phpstan-template TTemplateParam of array
 * @phpstan-template TSchema of array
 * @phpstan-extends EmailSource<array{token:AuthTokenModel,model:TeamModel2},array{holder:TeamHolder}>
 */
final class InfoEmail extends EmailSource
{
    private AuthTokenService $authTokenService;
    private LoginService $loginService;

    public function injectSecondary(
        AuthTokenService $authTokenService,
        LoginService $loginService
    ): void {
        $this->authTokenService = $authTokenService;
        $this->loginService = $loginService;
    }

    protected function getSource(array $params): array
    {
        $holder = $params['holder'];
        $lang = $holder->getModel()->game_lang->value;
        $emails = [];
        /** @var TeamMemberModel $member */
        foreach ($holder->getModel()->getMembers() as $member) {
            $emails[] = [
                'template' => [
                    'file' => __DIR__ . DIRECTORY_SEPARATOR . "member.info.$lang.latte",
                    'data' => [
                        'model' => $holder->getModel(),
                        'token' => $this->createToken($member->person, $holder),
                    ],
                ],
                'lang' => Language::from($holder->getModel()->game_lang->value),
                'data' => [
                    'recipient_person_id' => $member->person_id,
                    'sender' => 'Fyziklani <fyziklani@fykos.cz>'
                ],
            ];
        }
        /** @var TeamTeacherModel $teacher */
        foreach ($holder->getModel()->getTeachers() as $teacher) {
            $emails[] = [
                'template' => [
                    'file' => __DIR__ . DIRECTORY_SEPARATOR . "teacher.info.$lang.latte",
                    'data' => [
                        'model' => $holder->getModel(),
                        'token' => $this->createToken($teacher->person, $holder),
                    ],
                ],
                'lang' => Language::from($holder->getModel()->game_lang->value),
                'data' => [
                    'recipient_person_id' => $teacher->person_id,
                    'sender' => 'Fyziklani <fyziklani@fykos.cz>'
                ],
            ];
        }
        return $emails;
    }

    protected function createToken(PersonModel $person, TeamHolder $holder): AuthTokenModel
    {
        return $this->authTokenService->createToken(
            $person->getLogin() ?? $this->loginService->createLogin($person),
            AuthTokenType::from(AuthTokenType::EVENT_NOTIFY),
            $holder->getModel()->event->registration_end,
            null,
            true
        );
    }
}
