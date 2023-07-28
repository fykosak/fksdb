<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models;

use FKSDB\Models\PhoneNumber\InvalidPhoneNumberException;
use Fykosak\NetteORM\Model;
use Nette\Utils\Html;

/**
 * @property-read int $country_id
 * @property-read string $name
 * @property-read string $alpha_2
 * @property-read string $alpha_3
 * @property-read int|null $phone_nsn
 * @property-read string|null $phone_prefix
 */
final class CountryModel extends Model
{
    public function matchPhone(string $number): bool
    {
        if (\is_null($this->phone_prefix)) {
            return false;
        }
        if (\is_null($this->phone_nsn)) {
            return (bool)\preg_match('/^\\' . $this->phone_prefix . '\d+$/', $number);
        }
        return (bool)\preg_match('/^\\' . $this->phone_prefix . '\d{' . $this->phone_nsn . '}$/', $number);
    }

    public function getHtmlFlag(?string $className): Html
    {
        $className = $className . ' flag-icon flag-icon-' . \strtolower($this->alpha_2);
        return Html::el('i')->addAttributes(['class' => $className, 'title' => $this->name]);
    }

    /**
     * @throws InvalidPhoneNumberException
     */
    public function formatPhoneNumber(string $number): string
    {
        switch ($this->phone_nsn) {
            case 9:
                $regExp = '(\d{3})(\d{3})(\d{3})';
                break;
            case 10:
                $regExp = '(\d{2})(\d{4})(\d{4})';
                break;
            default:
                $regExp = '(\d+)';
        }

        if (preg_match('/^\\' . $this->phone_prefix . $regExp . '$/', $number, $matches)) {
            unset($matches[0]);
            return $this->phone_prefix . ' ' . \implode(' ', $matches);
        }
        throw new InvalidPhoneNumberException('number not match');
    }
}
