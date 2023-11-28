<?php

declare(strict_types=1);

namespace FKSDB\Models\Mail\FOF;

use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\Transitions\Callbacks\MailCallback;
use FKSDB\Models\Transitions\Holder\ModelHolder;
use FKSDB\Models\Transitions\Holder\TeamHolder;
use FKSDB\Models\Transitions\Transition\Transition;
use FKSDB\Modules\Core\Language;

/**
 * @phpstan-extends MailCallback<TeamHolder>
 */
class OrganizerTransitionMail extends MailCallback
{
    /** @phpstan-use OrganizerMailTrait<TeamHolder> */
    use OrganizerMailTrait;

    protected function getTemplatePath(ModelHolder $holder, Transition $transition): string
    {
        $transitionId = self::resolveLayoutName($transition);
        return __DIR__ . DIRECTORY_SEPARATOR . "teacher.$transitionId.cs.latte";
    }

    protected function getData(ModelHolder $holder): array
    {
        return MemberTransitionMail::getStaticData($holder);
    }

    /**
     * @phpstan-param TeamHolder|Transition<TeamHolder> $args
     * @throws BadTypeException
     */
    public function __invoke(...$args): void
    {
        /**
         * @phpstan-var TeamHolder $holder
         * @phpstan-var Transition<TeamHolder> $transition
         */
        [$holder, $transition] = $args;
        $data = $this->getData($holder);
        $data['recipient'] = 'Fyziklání <fyziklani@fykos.cz>';
        $data = array_merge(
            $data,
            $this->mailTemplateFactory->renderWithParameters(
                $this->getTemplatePath($holder, $transition),
                [
                    'logger' => $this->getMessageLog($holder),
                    'holder' => $holder,
                ],
                Language::tryFrom(Language::CS)
            )
        );
        $this->emailMessageService->addMessageToSend($data);
    }

    final protected function getPersons(ModelHolder $holder): array
    {
        return [];
    }
}
