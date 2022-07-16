<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Services;

use FKSDB\Models\ORM\Models\ModelEvent;
use Fykosak\NetteORM\Exceptions\ModelException;
use FKSDB\Models\ORM\Models\ModelAuthToken;
use FKSDB\Models\ORM\Models\ModelLogin;
use Fykosak\NetteORM\TypedSelection;
use Nette\Utils\DateTime;
use Nette\Utils\Random;
use Fykosak\NetteORM\Service;

class ServiceAuthToken extends Service
{

    private const TOKEN_LENGTH = 32; // for 62 characters ~ 128 bit

    /**
     * @throws ModelException
     */
    public function createToken(
        ModelLogin $login,
        string $type,
        ?\DateTimeInterface $until,
        ?string $data = null,
        bool $refresh = false,
        ?\DateTimeInterface $since = null
    ): ModelAuthToken {
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
            // TODO to related
            /** @var ModelAuthToken $token */
            $token = $this->getTable()
                ->where('login_id', $login->login_id)
                ->where('type', $type)
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

            $token = $this->createNewModel([
                'until' => $until,
                'login_id' => $login->login_id,
                'token' => $tokenData,
                'data' => $data,
                'since' => $since,
                'type' => $type,
            ]);
        } else {
            $this->updateModel($token, ['until' => $until]);
        }
        if (!$outerTransaction) {
            $this->explorer->getConnection()->commit();
        }

        return $token;
    }

    public function verifyToken(string $tokenData, bool $strict = true): ?ModelAuthToken
    {
        $tokens = $this->getTable()
            ->where('token', $tokenData);
        if ($strict) {
            $tokens->where('since <= NOW()')
                ->where('until IS NULL OR until >= NOW()');
        }
        /** @var ModelAuthToken $token */
        $token = $tokens->fetch();
        return $token;
    }

    /**
     * @param string|ModelAuthToken $token
     */
    public function disposeToken($token): void
    {
        if (!$token instanceof ModelAuthToken) {
            $token = $this->verifyToken($token);
        }
        if ($token) {
            $this->dispose($token);
        }
    }

    public function findTokensByEventId(ModelEvent $event): TypedSelection
    {
        return $this->getTable()
            ->where('type', ModelAuthToken::TYPE_EVENT_NOTIFY)
            ->where('since <= NOW()')
            ->where('until IS NULL OR until >= NOW()')
            ->where('data LIKE ?', $event->event_id . ':%');
    }
}
