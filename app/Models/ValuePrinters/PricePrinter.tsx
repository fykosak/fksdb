import { translator } from '@translator/translator';
import { Price } from 'FKSDB/Models/Payment/price';
import * as React from 'react';

interface OwnProps {
    price: Price;
}

export default class PricePrinter extends React.Component<OwnProps> {

    public render() {
        const {price: {EUR, CZK}} = this.props;
        if ((!EUR || +EUR.amount === 0) && (!CZK || +CZK.amount === 0)) {
            return <span>{translator.getText('for free')}</span>;
        }
        if (+EUR.amount === 0) {
            return <span>{CZK.amount} Kč</span>;
        }
        return <span>{EUR.amount} €/{CZK.amount} Kč</span>;
    }
}
