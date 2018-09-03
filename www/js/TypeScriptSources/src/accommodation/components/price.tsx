import * as React from 'react';
import { connect } from 'react-redux';
import PriceDisplay from '../../shared/components/displays/price';
import { getAccommodationPrice } from '../middleware/helpers';
import { IEventAccommodation } from '../middleware/interfaces';
import { IStore } from '../reducer';
import { IAccommodationState } from '../reducer/accommodation';

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

// import Lang from '../../../../../lang/components/lang';
// <Lang text={'Accommodation price'}/>

const mapDispatchToProps = (): IState => {
    return {};
};

const mapStateToProps = (state: IStore): IState => {
    return {
        accommodation: state.accommodation,
    };
};

export default connect(mapStateToProps, mapDispatchToProps)(Price);
