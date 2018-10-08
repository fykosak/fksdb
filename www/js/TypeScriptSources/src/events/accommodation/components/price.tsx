import * as React from 'react';
import { connect } from 'react-redux';
import PriceDisplay from '../../../shared/components/displays/price';
import { getAccommodationPrice } from '../middleware/helpers';
import { IEventAccommodation } from '../middleware/interfaces';
import { IAccommodationStore } from '../reducer/';
import { IAccommodationState } from '../reducer/accommodation';
import { lang } from '../../../i18n/i18n';

interface IProps {
    accommodationDef?: IEventAccommodation[];
}

interface IState {
    accommodation?: IAccommodationState;
}

class Price extends React.Component<IProps & IState, {}> {

    public render() {
        const {accommodationDef, accommodation} = this.props;
        const price = getAccommodationPrice(accommodationDef, accommodation);

        return <>
            <p>{lang.getText('Price')}</p>
            <PriceDisplay price={price}/>
        </>;
    }
}

const mapDispatchToProps = (): IState => {
    return {};
};

const mapStateToProps = (state: IAccommodationStore): IState => {
    return {
        accommodation: state.accommodation,
    };
};

export default connect(mapStateToProps, mapDispatchToProps)(Price);
