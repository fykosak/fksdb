import * as React from 'react';
import { IPrice } from '../../middleware/price';

interface IProps {
    price: IPrice;
}

export default class PriceDisplay extends React.Component<IProps, {}> {

    public render() {
        const {price: {eur, kc}} = this.props;
        return <span>{eur} €/{kc} Kč</span>;
    }
}
