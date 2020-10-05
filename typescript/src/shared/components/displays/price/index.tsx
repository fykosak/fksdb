import { lang } from '@i18n/i18n';
import * as React from 'react';
import { Price } from './interfaces';

interface OwnProps {
    price: Price;
}

export default class PriceDisplay extends React.Component<OwnProps, {}> {

    public render() {
        const {price: {eur, czk}} = this.props;
        if (+eur === 0 && +czk === 0) {
            return <span>{lang.getText('for free')}</span>;
        }
        if (+eur === 0) {
            return <span>{czk} Kč</span>;
        }
        return <span>{eur} €/{czk} Kč</span>;
    }
}
