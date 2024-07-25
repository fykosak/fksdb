<?php

declare(strict_types=1);

namespace FKSDB\Models\Email\Source\Payment;

use FKSDB\Models\Email\Source\TransitionEmailSource;
use FKSDB\Models\ORM\Models\EmailMessageTopic;
use FKSDB\Models\ORM\Models\PaymentModel;
use FKSDB\Models\Transitions\Callbacks\MailCallback;
use FKSDB\Models\Transitions\Holder\PaymentHolder;
use FKSDB\Models\Transitions\Transition\Transition;
use FKSDB\Modules\Core\Language;

/**
 * @phpstan-extends TransitionEmailSource<PaymentModel,array{model:PaymentModel}>
 */
class PaymentTransitionEmail extends TransitionEmailSource
{
    protected function getSource(array $params): array
    {
        /** @var PaymentHolder $holder */
        $holder = $params['holder'];
        /** @phpstan-var  Transition<PaymentHolder> $transition */
        $transition = $params['transition'];
        $lang = Language::from($holder->getModel()->person->getPreferredLang() ?? Language::EN);
        return [
            [
                'template' => [
                    'file' => __DIR__ . DIRECTORY_SEPARATOR
                        . MailCallback::resolveLayoutName($transition) . '.' . $lang->value . '.latte',
                    'data' => [
                        'model' => $holder->getModel()
                    ],
                ],
                'lang' => $lang,
                'data' => [
                    'recipient_person_id' => $holder->getModel()->person_id,
                    'blind_carbon_copy' => 'Fyziklání <fyziklani@fykos.cz>',
                    'sender' => 'Fyziklani <fyziklani@fykos.cz>',
                    'topic' => EmailMessageTopic::from(EmailMessageTopic::Internal),
                    'lang' => Language::from($holder->getModel()->person->getPreferredLang() ?? Language::EN),
                ],
            ]
        ];
    }
}
