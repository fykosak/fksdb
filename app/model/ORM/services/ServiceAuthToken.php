<?php

use Nette\DateTime;
use Nette\Utils\Strings;

/**
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class ServiceAuthToken extends AbstractServiceSingle {

    const TOKEN_LENGTH = 32; // for 62 characters ~ 128 bit

    protected $tableName = DbNames::TAB_AUTH_TOKEN;
    protected $modelClassName = 'ModelAuthToken';

    /**
     * 
     * @param string $type
     * @param \Nette\DateTime $until
     * @param \Nette\DateTime $since
     * @return ModelAuthToken
     */
    public function createToken(ModelLogin $login, $type, DateTime $until = null, $data = null, DateTime $since = null) {
        if ($since === null) {
            $since = new DateTime();
        }

        $this->getConnection()->beginTransaction();
        do {
            $tokenData = Strings::random(self::TOKEN_LENGTH, 'a-zA-Z0-9');
        } while ($this->verifyToken($tokenData));

        $token = $this->createNew(array(
            'login_id' => $login->login_id,
            'token' => $tokenData,
            'data' => $data,
            'since' => $since,
            'until' => $until,
            'type' => $type
        ));
        $this->save($token);
        $this->getConnection()->commit();

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
    
    
    //TODO garbage collection

}

