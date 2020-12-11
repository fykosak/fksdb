import PriceDisplay from '@shared/components/displays/price';
import { Price } from '@shared/components/displays/price/interfaces';
import * as React from 'react';
import { translator } from '@translator/Translator';

interface OwnProps {
    price: Price;
}

export default class PriceLabel extends React.Component<OwnProps, {}> {

    public render() {
        const {price} = this.props;
        return <small className="ml-3 price-label">
            {translator.getText('Price')}: <PriceDisplay price={price}/>
        </small>;
    }
}
