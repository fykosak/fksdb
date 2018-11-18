<?php


namespace FKSDB\EventPayment\PriceCalculator;


use Nette\OutOfRangeException;

class Price {

    const CURRENCY_EUR = 'eur';
    const CURRENCY_KC = 'kc';

    private $currency;
    private $amount = 0;

    public function __construct(float $amount = null, string $currency = null) {
        $this->amount = $amount;
        $this->currency = $currency;
    }

    public function add(Price $price) {
        if ($this->currency !== $price->getCurrency()) {
            throw new \LogicException('');
        }
        $this->amount += $price->getAmount();
    }

    public function setCurrency($currency) {
        $this->currency = $currency;
    }

    public function getCurrency() {
        return $this->currency;
    }

    public function getAmount() {
        return $this->amount;
    }

    public function addAmount(float $amount) {
        $this->amount += $amount;
    }


    public static function getAllCurrencies() {
        return [self::CURRENCY_KC, self::CURRENCY_EUR];
    }

    public static function getLabel($currency) {
        switch ($currency) {
            case self::CURRENCY_EUR:
                return '€';
            case self::CURRENCY_KC:
                return 'Kč';
            default:
                throw new OutOfRangeException(\sprintf(_('Currency %s doesnt exists'), $currency));
        }
    }
}
