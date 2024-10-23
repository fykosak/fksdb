<?php

declare(strict_types=1);

namespace FKSDB\Models\Email\Source\Payment;

use FKSDB\Models\Email\TransitionEmailSource;
use FKSDB\Models\ORM\Models\EmailMessageTopic;
use FKSDB\Models\ORM\Models\PaymentModel;
use FKSDB\Models\Transitions\Holder\ModelHolder;
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
                        . self::resolveLayoutName($transition) . '.' . $lang->value . '.latte',
                    'data' => [
                        'model' => $holder->getModel()
                    ],
                ],
                'data' => [
                    'recipient_person_id' => $holder->getModel()->person_id,
                    'blind_carbon_copy' => 'DSEF <dsef@fykos.cz>',
                    'sender' => 'DSEF <dsef@fykos.cz>',
                    'topic' => EmailMessageTopic::DSEF,
                    'lang' => Language::from($holder->getModel()->person->getPreferredLang() ?? Language::EN),
                ],
            ]
        ];
    }
    /**
     * @template TStaticHolder of ModelHolder
     * @phpstan-param  Transition<TStaticHolder> $transition
     */
    public static function resolveLayoutName(Transition $transition): string
    {
        return $transition->source->value . '->' . $transition->target->value;
    }
}
