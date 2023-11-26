<?php

declare(strict_types=1);

namespace FKSDB\Models\Mail\FOF;

use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Models\AuthTokenModel;
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
class OrganizerTransitionMail extends MailCallback
{
    /**
     * @param TeamHolder $holder
     * @phpstan-param Transition<TeamHolder> $transition
     */
    protected function getTemplatePath(ModelHolder $holder, Transition $transition): string
    {
        return __DIR__ . DIRECTORY_SEPARATOR . 'organizer.' . self::resolveLayoutName($transition);
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
        $data = $this->getData($holder, $transition);
        $data['recipient'] = 'Fyziklání <fyziklani@fykos.cz>';
        $data = array_merge(
            $data,
            $this->mailTemplateFactory->renderWithParameters2(
                $this->getTemplatePath($holder, $transition),
                [
                    'holder' => $holder,
                ],
                Language::tryFrom(Language::CS)
            )
        );
        $this->emailMessageService->addMessageToSend($data);
    }

    /**
     * @param TeamHolder $holder
     * @phpstan-param Transition<TeamHolder> $transition
     */
    protected function getData(ModelHolder $holder, Transition $transition): array
    {
        if ($holder->getModel()->game_lang->value === 'cs') {
            $sender = 'Fyziklání <fyziklani@fykos.cz>';
        } else {
            $sender = 'Fyziklani <fyziklani@fykos.cz>';
        }
        return [
            'sender' => $sender,
        ];
    }

    final protected function getPersons(ModelHolder $holder): array
    {
        return [];
    }

    protected function createToken(PersonModel $person, ModelHolder $holder): ?AuthTokenModel
    {
        return null;
    }
}
