import { Price } from 'FKSDB/Models/Payment/price';
import PricePrinter from 'FKSDB/Models/UI/price-printer';
import * as React from 'react';
import { Translator } from '@translator/translator';

interface OwnProps {
    price: Price;
    translator: Translator;
}

export default function PriceLabel({price, translator}: OwnProps) {
    return <small className="ms-3">
        {translator.getText('Price')}: <PricePrinter price={price} translator={translator}/>
    </small>;
}
