<?php

declare(strict_types=1);

namespace FKSDB\Models\Email\Source;

use FKSDB\Models\Email\TemplateFactory;
use FKSDB\Models\ORM\Models\EventParticipantModel;
use FKSDB\Models\ORM\Services\AuthTokenService;
use FKSDB\Models\ORM\Services\LoginService;
use FKSDB\Models\Transitions\Holder\ParticipantHolder;
use FKSDB\Models\Transitions\Transition\Transition;
use FKSDB\Modules\Core\Language;

/**
 * @phpstan-import-type TRenderedData from TemplateFactory
 * @phpstan-template TTemplateParam of array
 * @phpstan-extends TransitionEmailSource<EventParticipantModel,TTemplateParam>
 */
abstract class EventParticipantTransitionEmail extends TransitionEmailSource
{
    protected AuthTokenService $authTokenService;
    protected LoginService $loginService;

    public function injectTernary(
        AuthTokenService $authTokenService,
        LoginService $loginService
    ): void {
        $this->loginService = $loginService;
        $this->authTokenService = $authTokenService;
    }

    protected function getSource(array $params): array
    {
        /** @var ParticipantHolder $holder */
        $holder = $params['holder'];
        /** @phpstan-var  Transition<ParticipantHolder> $transition */
        $transition = $params['transition'];
        return [
            [
                'template' => [
                    'file' => $this->getTemplatePath($holder, $transition),
                    'data' => $this->getTemplateData($holder, $transition),
                ],
                'lang' => $this->getLang($holder, $transition),
                'data' => $this->getData($holder, $transition),
            ]
        ];
    }

    /**
     * @phpstan-param Transition<ParticipantHolder> $transition
     */
    abstract protected function getTemplatePath(ParticipantHolder $holder, Transition $transition): string;

    /**
     * @phpstan-param Transition<ParticipantHolder> $transition
     * @phpstan-return TTemplateParam
     */
    abstract protected function getTemplateData(ParticipantHolder $holder, Transition $transition): array;

    /**
     * @phpstan-param Transition<ParticipantHolder> $transition
     */
    abstract protected function getLang(ParticipantHolder $holder, Transition $transition): Language;

    /**
     * @phpstan-param Transition<ParticipantHolder> $transition
     * @phpstan-return array{
     *     recipient_person_id:int,
     *     blind_carbon_copy?:string,
     *     sender:string,
     *     reply_to?:string,
     * }
     */
    abstract protected function getData(ParticipantHolder $holder, Transition $transition): array;
}
