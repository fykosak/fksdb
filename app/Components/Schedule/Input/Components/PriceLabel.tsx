import { Price } from 'FKSDB/Models/Payment/price';
import PricePrinter from 'FKSDB/Models/ValuePrinters/PricePrinter';
import * as React from 'react';
import { TranslatorContext } from '@translator/LangContext';
import { availableLanguage, Translator } from '@translator/translator';

interface OwnProps {
    price: Price;
    translator: Translator<availableLanguage>;
}

export default class PriceLabel extends React.Component<OwnProps, never> {
    static contextType = TranslatorContext;

    public render() {
        const translator = this.context;
        const {price} = this.props;
        return <small className="ms-3">
            {translator.getText('Price')}: <PricePrinter price={price} translator={this.props.translator}/>
        </small>;
    }
}
