import { Price } from 'FKSDB/Models/Payment/price';
import * as React from 'react';
import { availableLanguage, Translator } from '@translator/translator';

interface OwnProps {
    price: Price;
    translator: Translator<availableLanguage>;
}

export default class PricePrinter extends React.Component<OwnProps, never> {

    public render() {
        const {price: {EUR, CZK}, translator} = this.props;
        if ((!EUR || +EUR.amount === 0) && (!CZK || +CZK.amount === 0)) {
            return <span>{translator.getText('for free')}</span>;
        }
        if (+EUR.amount === 0) {
            return <span>{CZK.amount} Kč</span>;
        }
        return <span>{EUR.amount} €/{CZK.amount} Kč</span>;
    }
}
