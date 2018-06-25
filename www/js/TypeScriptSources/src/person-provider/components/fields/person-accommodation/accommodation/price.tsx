import * as React from 'react';
import { connect } from 'react-redux';
import PriceDisplay from '../../../../../brawl-registration/components/displays/price';
import { FORM_NAME } from '../../../../../brawl-registration/components/form';
import {
    IPersonSelector,
} from '../../../../../brawl-registration/middleware/price';
import Lang from '../../../../../lang/components/lang';
import {
    getAccommodationFromState,
    getAccommodationPrice,
} from './helpers';
import { IAccommodationItem } from './interfaces';

interface IProps {
    personSelector: IPersonSelector;
}

interface IState {
    accommodation?: any;
    accommodationDef?: IAccommodationItem[];
}

class Price extends React.Component<IProps & IState, {}> {

    public render() {
        const {accommodationDef, accommodation} = this.props;
        const price = getAccommodationPrice(accommodationDef, accommodation);

        return <>
            <p><Lang text={'Accommodation price'}/></p>
            <PriceDisplay price={price}/>
        </>;
    }
}

const mapDispatchToProps = (): IState => {
    return {};
};

const mapStateToProps = (state, ownProps: IProps): IState => {
    return {
        accommodation: getAccommodationFromState(FORM_NAME, state, ownProps.personSelector),
        accommodationDef: state.definitions.accommodation,
    };
};

export default connect(mapStateToProps, mapDispatchToProps)(Price);
