<?php

use Nette\DateTime;
use Nette\Utils\Strings;

/**
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class ServiceAuthToken extends AbstractServiceSingle {

    const TOKEN_LENGTH = 20; // for 62 characters ~ 128 bit

    protected $tableName = DbNames::TAB_AUTH_TOKEN;
    protected $modelClassName = 'ModelAuthToken';

    /**
     * 
     * @param string $type
     * @param \Nette\DateTime $until
     * @param \Nette\DateTime $since
     * @return ModelAuthToken
     */
    public function createToken(ModelLogin $login, $type, DateTime $until = null, DateTime $since = null) {
        if ($since === null) {
            $since = new DateTime();
        }

        // Is this thread-safe? The method may fail on unique constraint...
        do {
            $tokenData = Strings::random(self::TOKEN_LENGTH, 'a-zA-Z0-9');
        } while ($this->verifyToken($tokenData));

        $token = $this->createNew(array(
            'login_id' => $login->login_id,
            'token' => $tokenData,
            'since' => $since,
            'until' => $until,
            'type' => $type
        ));
        $this->save($token);
        return $token;
    }

    /**
     * 
     * @param string $token
     * @return ModelAuthToken|null
     */
    public function verifyToken($tokenData) {
        $tokens = $this->getTable()
                ->where('token = ?', $tokenData)
                ->where('since <= NOW()')
                ->where('until IS NULL OR until >= NOW()');

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

}

