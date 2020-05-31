<?php

namespace FKSDB\Payment;

use FKSDB\Payment\PriceCalculator\UnsupportedCurrencyException;
use LogicException;

/**
 * Class Price
 * @author Michal Červeňák <miso@fykos.cz>
 */
class Price {

    public const CURRENCY_EUR = 'eur';
    public const CURRENCY_CZK = 'czk';

    private string $currency;

    private float $amount;

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
    public function add(Price $price): void {
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

    public function addAmount(float $amount): void {
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
