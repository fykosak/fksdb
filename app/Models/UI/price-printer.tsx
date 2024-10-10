import { Price } from 'FKSDB/Models/Payment/price';
import * as React from 'react';
import { Translator } from '@translator/translator';

interface OwnProps {
    price: Price;
    translator: Translator;
}

export default function PricePrinter({price, translator}: OwnProps) {

    const labels = [];
    if (Object.hasOwn(price, 'CZK')) {
        labels.push(<span className="me-2">{price.CZK.amount} {translator.getText('CZK')}</span>)
    }
    if (Object.hasOwn(price, 'EUR')) {
        labels.push(<span className="me-2">{price.EUR.amount} {translator.getText('EUR')}</span>)
    }
    if (!labels.length) {
        return <span>{translator.getText('for free')}</span>;
    }
    return <span>{labels}</span>;
}
