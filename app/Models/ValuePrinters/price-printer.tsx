import { Price } from 'FKSDB/Models/Payment/price';
import * as React from 'react';
import { availableLanguage, Translator } from '@translator/translator';

interface OwnProps {
    price: Price;
    translator: Translator<availableLanguage>;
}

export default function PricePrinter(props: OwnProps) {
    const {price: {EUR, CZK}, translator} = props;
    if ((!EUR || +EUR.amount === 0) && (!CZK || +CZK.amount === 0)) {
        return <span>{translator.getText('for free')}</span>;
    }
    if (+EUR.amount === 0) {
        return <span>{CZK.amount} Kč</span>;
    }
    return <span>{EUR.amount} €/{CZK.amount} Kč</span>;
}
