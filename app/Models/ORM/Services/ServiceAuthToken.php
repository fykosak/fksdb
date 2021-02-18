<?php

namespace FKSDB\Models\ORM\Services;

use FKSDB\Models\Exceptions\ModelException;
use FKSDB\Models\ORM\Models\ModelAuthToken;
use FKSDB\Models\ORM\Models\ModelLogin;
use Nette\Utils\DateTime;
use Nette\Utils\Random;

/**
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class ServiceAuthToken extends AbstractServiceSingle {

    private const TOKEN_LENGTH = 32; // for 62 characters ~ 128 bit

    /**
     *
     * @param ModelLogin $login
     * @param string $type
     * @param \DateTimeInterface|null $until
     * @param null|string $data
     * @param bool $refresh
     * @param \DateTimeInterface|null $since
     * @return ModelAuthToken
     * @throws ModelException
     */
    public function createToken(ModelLogin $login, string $type, ?\DateTimeInterface $until, ?string $data = null, bool $refresh = false, ?\DateTimeInterface $since = null): ModelAuthToken {
        if ($since === null) {
            $since = new DateTime();
        }

        $connection = $this->context->getConnection();
        $outerTransaction = false;
        if ($connection->getPdo()->inTransaction()) {
            $outerTransaction = true;
        } else {
            $connection->beginTransaction();
        }

        if ($refresh) {
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
            $this->updateModel2($token, ['until' => $until]);
        }
        //  $token->until = $until;

        // $this->save($token);
        if (!$outerTransaction) {
            $this->context->getConnection()->commit();
        }

        return $token;
    }

    public function verifyToken(string $tokenData, bool $strict = true): ?ModelAuthToken {
        $tokens = $this->getTable()
            ->where('token', $tokenData);
        if ($strict) {
            $tokens->where('since <= NOW()')
                ->where('until IS NULL OR until >= NOW()');
        }
        /** @var ModelAuthToken $token */
        $token = $tokens->fetch();
        return $token ?: null;
    }

    /**
     * @param string|ModelAuthToken $token
     * @return void
     */
    public function disposeToken($token): void {
        if (!$token instanceof ModelAuthToken) {
            $token = $this->verifyToken($token);
        }
        if ($token) {
            $this->dispose($token);
        }
    }

    public function findTokensByEventId(int $eventId): array {
        $res = $this->getTable()
            ->where('type', ModelAuthToken::TYPE_EVENT_NOTIFY)
            ->where('since <= NOW()')
            ->where('until IS NULL OR until >= NOW()')
            ->where('data LIKE ?', $eventId . ':%');
        $tokens = [];
        foreach ($res as $token) {
            $tokens[] = ModelAuthToken::createFromActiveRow($token);
        }
        return $tokens;
    }
}
