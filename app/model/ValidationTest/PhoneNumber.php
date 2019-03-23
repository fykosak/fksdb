<?php

namespace FKSDB\ValidationTest;

use FKSDB\ORM\Models\ModelPerson;
use Nette\NotImplementedException;

/**
 * Class PhoneNumber
 * @package FKSDB\ValidationTest
 */
class PhoneNumber extends ValidationTest {
    /**
     * @return string
     */
    public function getTitle(): string {
        return _('Phone number');
    }

    /**
     * @return string
     */
    public function getAction(): string {
        throw new NotImplementedException();
    }

    /**
     * @param ModelPerson $person
     * @return array
     */
    public static function run(ModelPerson $person): array {
        throw new NotImplementedException();
        /*   $info = $person->getInfo();
           if ($info) {
               if ($info->phone){

               }
           }*/
    }

}
