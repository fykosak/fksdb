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
     * @throws \PDOException
     */
    public function createToken(
        LoginModel $login,
        AuthTokenType $type,
        ?\DateTimeInterface $until,
        ?string $data = null,
        bool $refresh = false,
        ?\DateTimeInterface $since = null
    ): AuthTokenModel {
        if ($since === null) {
            $since = new DateTime();
        }

        $connection = $this->explorer->getConnection();
        $outerTransaction = false;
        if ($connection->getPdo()->inTransaction()) {
            $outerTransaction = true;
        } else {
            $connection->beginTransaction();
        }

        if ($refresh) {
            /** @var AuthTokenModel|null $token */
            $token = $login->getTokens($type)
                ->where('data', $data)
                ->where('since <= NOW()')
                ->where('until IS NULL OR until >= NOW()')
                ->fetch();
        } else {
            $token = null;
        }
        if (!$token) {
            do {
                $tokenData = Random::generate(self::TOKEN_LENGTH, 'a-zA-Z0-9');
            } while ($this->verifyToken($tokenData));

            $token = $this->storeModel([
                'until' => $until,
                'login_id' => $login->login_id,
                'token' => $tokenData,
                'data' => $data,
                'since' => $since,
                'type' => $type,
            ]);
        } else {
            $this->storeModel(['until' => $until], $token);
        }
        if (!$outerTransaction) {
            $connection->commit();
        }

        return $token;
    }

    public function createUnsubscribeToken(LoginModel $login): AuthTokenModel
    {
        $connection = $this->explorer->getConnection();
        $outerTransaction = false;
        if ($connection->getPdo()->inTransaction()) {
            $outerTransaction = true;
        } else {
            $connection->beginTransaction();
        }
        /** @var AuthTokenModel|null $token */
        $token = $this->getTable()->where('type', AuthTokenType::UNSUBSCRIBE)->fetch();
        if (!$token) {
            $token = $this->create([
                'login_id' => $login->login_id,
                'type' => AuthTokenType::UNSUBSCRIBE,
                'until' => (new \DateTime())->modify('+1 year'),
            ]);
        }
        if (!$outerTransaction) {
            $connection->commit();
        }
        return $token;
    }

    public function createEventToken(LoginModel $login, EventModel $event): AuthTokenModel
    {
        $connection = $this->explorer->getConnection();
        $outerTransaction = false;
        if ($connection->getPdo()->inTransaction()) {
            $outerTransaction = true;
        } else {
            $connection->beginTransaction();
        }
        /** @var AuthTokenModel|null $token */
        $token = $this->getTable()
            ->where('type', AuthTokenType::EVENT_NOTIFY)
            ->where('data', $event->event_id)
            ->fetch();
        if (!$token) {
            $token = $this->create([
                'login_id' => $login->login_id,
                'type' => AuthTokenType::EVENT_NOTIFY,
                'data' => (string)$event->event_id,
                'until' => $event->registration_end,
                'since' => $event->registration_begin,
            ]);
        }
        if (!$outerTransaction) {
            $connection->commit();
        }
        return $token;
    }

    /**
     * @phpstan-param array{
     *     login_id:int,
     *     type:string,
     *     data?:string,
     *     until?:\DateTimeInterface,
     *     since?:\DateTimeInterface,
     * } $data
     */
    private function create(array $data): AuthTokenModel
    {
        do {
            $tokenData = Random::generate(self::TOKEN_LENGTH, 'a-zA-Z0-9');
        } while ($this->verifyToken($tokenData));
        return $this->storeModel(array_merge($data, ['token' => $tokenData]));
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
            ->where('type', AuthTokenType::EVENT_NOTIFY)
            ->where('data', $event->event_id);
    }
}
