import * as React from 'react';
import { lang } from '../../../../i18n/i18n';
import { IPrice } from './interfaces';

interface IProps {
    price: IPrice;
}

export default class PriceDisplay extends React.Component<IProps, {}> {

    public render() {
        const {price: {eur, kc}} = this.props;
        if (eur === 0 && kc === 0) {
            return <span>{lang.getText('for free')}</span>;
        }
        if (eur === 0) {
            return <span>{kc} Kč</span>;
        }
        return <span>{eur} €/{kc} Kč</span>;
    }
}
