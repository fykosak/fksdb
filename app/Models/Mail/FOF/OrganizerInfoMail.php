<?php

declare(strict_types=1);

namespace FKSDB\Models\Mail\FOF;

use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\Transitions\Holder\TeamHolder;
use FKSDB\Modules\Core\Language;

class OrganizerInfoMail extends InfoEmail
{
    protected function getTemplatePath(TeamHolder $holder): string
    {
        return __DIR__ . DIRECTORY_SEPARATOR . 'organizer.info';
    }

    protected function getData(TeamHolder $holder): array
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

    /**
     * @throws BadTypeException
     */
    public function __invoke(...$args): void
    {
        /**
         * @var TeamHolder $holder
         */
        [$holder] = $args;
        $data = $this->getData($holder);
        $data['recipient'] = 'Fyziklání <fyziklani@fykos.cz>';
        $data = array_merge(
            $data,
            $this->mailTemplateFactory->renderWithParameters2(
                $this->getTemplatePath($holder),
                [
                    'holder' => $holder,
                ],
                Language::tryFrom(Language::CS)
            )
        );
        $this->emailMessageService->addMessageToSend($data);
    }

    protected function getPersons(TeamHolder $holder): array
    {
        return [];
    }
}
