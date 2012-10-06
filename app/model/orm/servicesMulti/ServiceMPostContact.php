<?php

/**
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class ServiceMPostContact extends AbstractServiceMulti {

    protected static $staticMainServiceName = 'ServiceAddress';
    protected static $staticJoinedServiceName = 'ServicePostContact';
    protected static $staticModelClassName = 'ModelMPostContact';

    /**
     * @param NConnection $connection
     * @return ServiceAction
     */
    public static function getInstance(NConnection $connection = null) {
        if (!isset(self::$instances[self::$staticModelClassName])) {
            $mainService = call_user_func(self::$staticMainServiceName . '::getInstance', $connection);
            $joinedService = call_user_func(self::$staticJoinedServiceName . '::getInstance', $connection);

            self::$instances[self::$staticModelClassName] = new self($mainService, $joinedService, self::$staticModelClassName);
        }
        return self::$instances[self::$staticModelClassName];
    }

}

?>
