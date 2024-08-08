<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Services;

use FKSDB\Models\ORM\Models\AuthTokenModel;
use FKSDB\Models\ORM\Models\AuthTokenType;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Models\LoginModel;
use Fykosak\NetteORM\Selection\TypedSelection;
use Fykosak\NetteORM\Service\Service;
use Nette\Utils\DateTime;
use Nette\Utils\Random;

/**
 * @phpstan-extends Service<AuthTokenModel>
 */
final class AuthTokenService extends Service
{

    private const TOKEN_LENGTH = 32; // for 62 characters ~ 128 bit

    public function createToken2(
        LoginModel $login,
        AuthTokenType $type,
        ?\DateTimeInterface $since,
        \DateTimeInterface $until,
        ?string $data = null
    ): AuthTokenModel {
        $connection = $this->explorer->getConnection();
        $outerTransaction = false;
        if ($connection->getPdo()->inTransaction()) {
            $outerTransaction = true;
        } else {
            $connection->beginTransaction();
        }

        if ($type->refresh()) {
            $query = $this->getTable()
                ->where('type', AuthTokenType::Unsubscribe);
            if (isset($data)) {
                $query->where('data', $data);
            }
            /** @var AuthTokenModel|null $token */
            $token = $query->fetch();
        }

        $data = [
            'login_id' => $login->login_id,
            'type' => $type->value,
            'data' => $data,
            'since' => $since ?? new DateTime(),
            'until' => $until,
        ];
        if (isset($token)) {
            $token = $this->storeModel($data, $token);
        } else {
            do {
                $tokenData = Random::generate(self::TOKEN_LENGTH, 'a-zA-Z0-9');
            } while ($this->verifyToken($tokenData));
            $token = $this->storeModel(array_merge($data, ['token' => $tokenData]));
        }

        if (!$outerTransaction) {
            $connection->commit();
        }

        return $token;
    }

    public function createUnsubscribeToken(LoginModel $login): AuthTokenModel
    {
        return $this->createToken2(
            $login,
            AuthTokenType::from(AuthTokenType::Unsubscribe),
            new DateTime(),
            (new \DateTime())->modify('+1 year')
        );
    }

    public function createEventToken(LoginModel $login, EventModel $event): AuthTokenModel
    {
        return $this->createToken2(
            $login,
            AuthTokenType::from(AuthTokenType::EventNotify),
            $event->registration_begin,
            $event->registration_end,
            (string)$event->event_id
        );
    }

    public function verifyToken(string $tokenData, bool $strict = true): ?AuthTokenModel
    {
        $tokens = $this->getTable()->where('token', $tokenData);
        if ($strict) {
            $tokens->where('since <= NOW()')->where('until IS NULL OR until >= NOW()');
        }
        /** @var AuthTokenModel|null $token */
        $token = $tokens->fetch();
        return $token;
    }

    /**
     * @param string|AuthTokenModel $token
     */
    public function disposeToken($token): void
    {
        if (!$token instanceof AuthTokenModel) {
            $token = $this->verifyToken($token);
        }
        if ($token) {
            $this->disposeModel($token);
        }
    }

    /**
     * @phpstan-return TypedSelection<AuthTokenModel>
     */
    public function findTokensByEvent(EventModel $event): TypedSelection
    {
        return $this->getTable()
            ->where('type', AuthTokenType::EventNotify)
            ->where('data', $event->event_id);
    }
}
