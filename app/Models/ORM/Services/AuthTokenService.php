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

    /**
     * @throws \Throwable
     */
    public function createToken(
        LoginModel $login,
        AuthTokenType $type,
        \DateTimeInterface $since,
        \DateTimeInterface $until,
        ?string $data = null
    ): AuthTokenModel {
        return $this->explorer->getConnection()->transaction(
            function () use ($login, $type, $since, $until, $data): AuthTokenModel {
                if ($type->refresh()) {
                    $query = $login->getTokens($type);
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
                    'since' => $since,
                    'until' => $until,
                ];
                if (isset($token)) {
                    return $this->storeModel($data, $token);
                } else {
                    do {
                        $tokenData = Random::generate(self::TOKEN_LENGTH, 'a-zA-Z0-9');
                    } while ($this->findToken($tokenData));
                    return $this->storeModel(array_merge($data, ['token' => $tokenData]));
                }
            }
        );
    }

    /**
     * @throws \Throwable
     */
    public function createUnsubscribeToken(LoginModel $login): AuthTokenModel
    {
        return $this->createToken(
            $login,
            AuthTokenType::from(AuthTokenType::Unsubscribe),
            new DateTime(),
            (new \DateTime())->modify('+1 year')
        );
    }

    /**
     * @throws \Throwable
     */
    public function createEventToken(LoginModel $login, EventModel $event): AuthTokenModel
    {
        return $this->createToken(
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

    public function findToken(string $tokenData): ?AuthTokenModel
    {
        $tokens = $this->getTable()->where('token', $tokenData);
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
            $token = $this->findToken($token);
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
