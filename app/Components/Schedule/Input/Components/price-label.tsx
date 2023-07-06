import { Price } from 'FKSDB/Models/Payment/price';
import PricePrinter from 'FKSDB/Models/ValuePrinters/price-printer';
import * as React from 'react';
import { availableLanguage, Translator } from '@translator/translator';

interface OwnProps {
    price: Price;
    translator: Translator<availableLanguage>;
}

export default function PriceLabel(props: OwnProps) {
    const {price, translator} = props;
    return <small className="ms-3">
        {translator.getText('Price')}: <PricePrinter price={price} translator={translator}/>
    </small>;
}
