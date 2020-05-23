<?php

namespace FKSDB\Payment;

use FKSDB\Payment\PriceCalculator\UnsupportedCurrencyException;
use LogicException;

/**
 * Class Price
 * @package FKSDB\Payment\PriceCalculator
 */
class Price {

    const CURRENCY_EUR = 'eur';
    const CURRENCY_CZK = 'czk';
    /**
     * @var string
     */
    private $currency;
    /**
     * @var float
     */
    private $amount;

    /**
     * Price constructor.
     * @param float $amount
     * @param string $currency
     */
    public function __construct(float $amount, string $currency) {
        $this->amount = $amount;
        $this->currency = $currency;
    }

    /**
     * @param Price $price
     * @throws LogicException
     */
    public function add(Price $price) {
        if ($this->currency !== $price->getCurrency()) {
            throw new LogicException('Currencies are not a same');
        }
        $this->amount += $price->getAmount();
    }

    public function getCurrency(): string {
        return $this->currency;
    }

    public function getAmount(): float {
        return $this->amount;
    }

    /**
     * @param float $amount
     * @return void
     */
    public function addAmount(float $amount) {
        $this->amount += $amount;
    }

    /**
     * @return string[]
     */
    public static function getAllCurrencies(): array {
        return [self::CURRENCY_CZK, self::CURRENCY_EUR];
    }

    /**
     * @param $currency
     * @return string
     * @throws UnsupportedCurrencyException
     */
    public static function getLabel($currency): string {
        switch ($currency) {
            case self::CURRENCY_EUR:
                return '€';
            case self::CURRENCY_CZK:
                return 'Kč';
            default:
                throw new UnsupportedCurrencyException($currency);
        }
    }

    /**
     * @return string
     * @throws UnsupportedCurrencyException
     */
    public function __toString(): string {
        return \sprintf('%1.2f %s', $this->amount, self::getLabel($this->currency));
    }
}
