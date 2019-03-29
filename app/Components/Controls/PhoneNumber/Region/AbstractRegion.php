<?php

namespace FKSDB\Components\Controls\PhoneNumber\Region;

use FKSDB\Components\Controls\PhoneNumber\InvalidPhoneNumberException;
use Nette\Utils\Html;

/**
 * Class AbstractRegion
 * @package FKSDB\Components\Controls\PhoneNumber\Region
 */
abstract class AbstractRegion {
    /**
     * @param string $number
     * @return Html
     * @throws InvalidPhoneNumberException
     */
    abstract public static function create(string $number): Html;

    /**
     * @return string
     */
    abstract protected static function getPrefix(): string;

    /**
     * @return int
     */
    abstract protected static function getNSN(): int;

    /**
     * @return string
     */
    abstract protected static function getISO3166(): string;

    /**
     * @param $number
     * @return Html
     */
    protected static function createHtml(string $number): Html {
        $flag = Html::el('span')
            ->addAttributes(['class' => 'phone-flag mr-3'])
            ->addHtml(Html::el('img')
                ->addAttributes(['src' => '/images/flags/4x3/' . static::getISO3166() . '.svg']));
        return Html::el('span')->addAttributes([])->addHtml($flag)->addText(static::getPrefix() . ' ' . $number);
    }

    /**
     * @param string $number
     * @return false
     */
    public static function match(string $number): bool {
        return !!\preg_match('/^\\' . static::getPrefix() . '\d{' . static::getNSN() . '}/', $number);
    }
}
