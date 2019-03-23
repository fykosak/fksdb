<?php

namespace FKSDB\ValidationTest\Tests;

use FKSDB\Components\Controls\PhoneNumber\PhoneNumberFactory;
use FKSDB\Components\Grids\Validation\ValidationGrid;
use FKSDB\ORM\Models\ModelPerson;
use FKSDB\ValidationTest\ValidationLog;
use FKSDB\ValidationTest\ValidationTest;
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
        $log = [];
        $info = $person->getInfo();
        if (!$info) {
            return [];
        }
        $keys = ['phone', 'phone_parent_d', 'phone_parent_m'];
        foreach ($keys as $key) {
            $value = $info->{$key};
            if ($value) {
                if (!PhoneNumberFactory::isValid($value)) {
                    $log[] = new ValidationLog(\sprintf('%s %s is not valid', $key, $value), 'danger');
                } else {
                    $log[] = new ValidationLog(\sprintf('%s is valid', $key), 'success');
                }
            }
        }
        return $log;
    }

    /**
     * @param ValidationGrid $grid
     * @throws \NiftyGrid\DuplicateColumnException
     */
    public static function configureGrid(ValidationGrid $grid) {
        $keys = ['phone', 'phone_parent_d', 'phone_parent_m'];
        foreach ($keys as $key) {
            $grid->addColumn('phone_number__' . $key, \sprintf(_('Phone number test :: %s'), $key))->setRenderer(function ($row) use ($key) {
                $person = ModelPerson::createFromTableRow($row);
                $log = self::validate($key, $person);
                return self::createHtml($log);
            });
        }

    }

    /**
     * @param string $key
     * @param ModelPerson $person
     * @return ValidationLog
     */
    private static function validate(string $key, ModelPerson $person): ValidationLog {
        $info = $person->getInfo();
        if (!$info) {
            return new ValidationLog('Person info is not set', 'info');
        }
        $value = $info->{$key};
        if (!$value) {
            return new ValidationLog($key . ' info is not set', 'info');
        }
        if (!PhoneNumberFactory::isValid($value)) {
            return new ValidationLog(\sprintf('%s is not valid', $key), 'danger');
        } else {
            return new ValidationLog(\sprintf('%s is valid', $key), 'success');
        }
    }


}
