<?php

declare(strict_types=1);

namespace FKSDB\Modules\PublicModule;

use FKSDB\Models\ORM\Models\AuthTokenType;
use Nette\InvalidArgumentException;

final class ApplicationPresenter extends BasePresenter
{
    public const PARAM_AFTER = 'a';

    public function actionDefault(?int $eventId, ?int $id): void
    {
        if (
            $this->tokenAuthenticator->isAuthenticatedByToken(
                AuthTokenType::from(AuthTokenType::EVENT_NOTIFY)
            )
        ) {
            $data = $this->tokenAuthenticator->getTokenData();
            if ($data) {
                $this->tokenAuthenticator->disposeTokenData();
                $this->redirect('this', self::decodeParameters($data));
            }
        }
    }

    /**
     * @phpstan-return int[]
     */
    public static function decodeParameters(string $data): array
    {
        $parts = explode(':', $data);
        if (count($parts) != 2) {
            throw new InvalidArgumentException("Cannot decode '$data'.");
        }
        return [
            'eventId' => (int)$parts[0],
            'id' => (int)$parts[1],
        ];
    }
}
