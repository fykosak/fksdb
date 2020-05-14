<?php

namespace FKSDB\ORM\Services;

use FKSDB\ORM\AbstractServiceSingle;
use FKSDB\ORM\DbNames;
use FKSDB\ORM\Models\ModelAuthToken;
use FKSDB\ORM\Models\ModelLogin;
use Nette\Utils\DateTime;
use Nette\Utils\Random;

/**
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class ServiceAuthToken extends AbstractServiceSingle {

    const TOKEN_LENGTH = 32; // for 62 characters ~ 128 bit

    /**
     * @return string
     */
    public function getModelClassName(): string {
        return ModelAuthToken::class;
    }

    /**
     * @return string
     */
    protected function getTableName(): string {
        return DbNames::TAB_AUTH_TOKEN;
    }

    /**
     *
     * @param ModelLogin $login
     * @param string $type
     * @param DateTime $until
     * @param null $data
     * @param bool $refresh
     * @param DateTime $since
     * @return ModelAuthToken
     * @throws \Exception
     */
    public function createToken(ModelLogin $login, $type, DateTime $until = null, $data = null, $refresh = false, DateTime $since = null) {
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
                'type' => $type
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

    /**
     *
     * @param $tokenData
     * @param bool $strict
     * @return ModelAuthToken|null
     */
    public function verifyToken($tokenData, $strict = true) {
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
     *
     * @param $token
     */
    public function disposeToken($token) {
        if (!$token instanceof ModelAuthToken) {
            $token = $this->verifyToken($token);
        }
        if ($token) {
            $this->dispose($token);
        }
    }

    /**
     * @param $eventId
     * @return array
     */
    public function findTokensByEventId($eventId) {
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
