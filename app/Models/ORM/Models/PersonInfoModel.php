<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models;

use FKSDB\Models\ORM\Tests\PersonInfo\PersonInfoFileLevelTest;
use FKSDB\Models\ORM\Tests\Test;
use FKSDB\Modules\Core\Language;
use Fykosak\NetteORM\Model\Model;
use Nette\DI\Container;
use Nette\Utils\DateTime;

/**
 * @property-read int $person_id
 * @property-read PersonModel $person
 * @property-read string|null $preferred_lang # TODO enum
 * @property-read DateTime|null $born
 * @property-read string|null $id_number
 * @property-read string|null $born_id
 * @property-read string|null $phone
 * @property-read string|null $im
 * @property-read string|null $note
 * @property-read string|null $uk_login
 * @property-read string|null $isic_number
 * @property-read string|null $account
 * @property-read DateTime|null $agreed
 * @property-read string|null $birthplace
 * @property-read string|null $citizenship
 * @property-read int|null $health_insurance
 * @property-read string|null $employer
 * @property-read string|null $academic_degree_prefix
 * @property-read string|null $academic_degree_suffix
 * @property-read string|null $email
 * @property-read string|null $origin
 * @property-read string|null $career
 * @property-read string|null $homepage
 * @property-read string|null $fb_id
 * @property-read string|null $linkedin_id
 * @property-read string|null $phone_parent_d
 * @property-read string|null $phone_parent_m
 * @property-read string|null $duplicates
 * @property-read string|null $email_parent_d
 * @property-read string|null $email_parent_m
 * @property-read string|null $pizza
 * @property-read string|null $theme
 */
final class PersonInfoModel extends Model
{
    /**
     * @return Language|mixed|null
     * @throws \ReflectionException
     */
    public function &__get(string $key)
    {
        $value = parent::__get($key);
        switch ($key) {
            case 'preferred_lang':
                $value = Language::tryFrom($value);
                break;
        }
        return $value;
    }

    /**
     * @phpstan-return Test<self>[]
     */
    public static function getTests(Container $container): array
    {
        return [
            new PersonInfoFileLevelTest('phone', $container),
            new PersonInfoFileLevelTest('phone_parent_d', $container),
            new PersonInfoFileLevelTest('phone_parent_m', $container),
        ];
    }
}
