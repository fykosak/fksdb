<?php

use FKSDB\ORM\ModelAuthToken;
use FKSDB\ORM\ModelLogin;
use Nette\DateTime;
use Nette\Utils\Strings;

/**
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class ServiceAuthToken extends AbstractServiceSingle {

    const TOKEN_LENGTH = 32; // for 62 characters ~ 128 bit

    protected $tableName = DbNames::TAB_AUTH_TOKEN;
    protected $modelClassName = 'FKSDB\ORM\ModelAuthToken';

    /**
     *
     * @param string $type
     * @param \Nette\DateTime $until
     * @param \Nette\DateTime $since
     * @param ModelLogin $login
     * @return ModelAuthToken
     */
    public function createToken(ModelLogin $login, $type, DateTime $until = null, $data = null, $refresh = false, DateTime $since = null) {
        if ($since === null) {
            $since = new DateTime();
        }

        $connection = $this->getConnection();
        $outerTransaction = false;
        if (!$connection->inTransaction()) {
            $this->getConnection()->beginTransaction();
        } else {
            $outerTransaction = true;
        }

        if ($refresh) {
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
                $tokenData = Strings::random(self::TOKEN_LENGTH, 'a-zA-Z0-9');
            } while ($this->verifyToken($tokenData));

            $token = $this->createNew([
                'login_id' => $login->login_id,
                'token' => $tokenData,
                'data' => $data,
                'since' => $since,
                'type' => $type
            ]);
        }
        $token->until = $until;

        $this->save($token);
        if (!$outerTransaction) {
            $this->getConnection()->commit();
        }

        return $token;
    }

    /**
     *
     * @param string $token
     * @partm bool $strict
     * @return ModelAuthToken|null
     */
    public function verifyToken($tokenData, $strict = true) {
        $tokens = $this->getTable()
                ->where('token', $tokenData);
        if ($strict) {
            $tokens->where('since <= NOW()')
                    ->where('until IS NULL OR until >= NOW()');
        }


        $token = $tokens->fetch();
        if (!$token) {
            return null;
        } else {
            return $token;
        }
    }

    /**
     *
     * @param ModelAuthToken|string $tokenData
     */
    public function disposeToken($token) {
        if (!$token instanceof ModelAuthToken) {
            $token = $this->verifyToken($token);
        }
        if ($token) {
            $this->dispose($token);
        }
    }

    public function findTokensByEventId($eventId) {
        $res = $this->getTable()
            ->where('type', ModelAuthToken::TYPE_EVENT_NOTIFY)
            ->where('since <= NOW()')
            ->where('until IS NULL OR until >= NOW()')
            ->where('data LIKE ?', $eventId.':%');
        $tokens = [];
        foreach($res as $token) {
            $tokens[] = ModelAuthToken::createFromTableRow($token);
        }
        return $tokens;
    }

    //TODO garbage collection
}

