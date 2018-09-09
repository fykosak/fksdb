import * as React from 'react';
import { connect } from 'react-redux';
import PriceDisplay from '../../shared/components/displays/price';
import { getAccommodationPrice } from '../middleware/helpers';
import { IEventAccommodation } from '../middleware/interfaces';
import { IAccommodationState } from '../reducer/accommodation';
import { IAccommodationStore } from '../reducer';

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
            <p>Price</p>
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
