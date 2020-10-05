import { lang } from '@i18n/i18n';
import PriceDisplay from '@shared/components/displays/price';
import { Price } from '@shared/components/displays/price/interfaces';
import * as React from 'react';

interface OwnProps {
    price: Price;
}

export default class PriceLabel extends React.Component<OwnProps, {}> {

    public render() {
        const {price} = this.props;
        return <small className="ml-3 price-label">
            {lang.getText('Price')}: <PriceDisplay price={price}/>
        </small>;
    }
}
