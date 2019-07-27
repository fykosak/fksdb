import * as React from 'react';
import { connect } from 'react-redux';
import { lang } from '@i18n/i18n';
import { InputConnectorItems } from '../../../input-connector/reducers/';
import PriceDisplay from '../../../shared/components/displays/price/Index';
import { getAccommodationPrice } from '../middleware/helpers';
import { EventAccommodation } from '../middleware/interfaces';
import { Store } from '../reducer/';

interface Props {
    accommodationDef?: EventAccommodation[];
}

interface State {
    accommodation?: InputConnectorItems;
}

class Price extends React.Component<Props & State, {}> {

    public render() {
        const {accommodationDef, accommodation} = this.props;
        const price = getAccommodationPrice(accommodationDef, accommodation);

        return <>
            <p>{lang.getText('Price')}</p>
            <PriceDisplay price={price}/>
        </>;
    }
}

const mapDispatchToProps = (): State => {
    return {};
};

const mapStateToProps = (state: Store): State => {
    return {
        accommodation: state.inputConnector.data,
    };
};

export default connect(mapStateToProps, mapDispatchToProps)(Price);
