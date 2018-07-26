/*import * as React from 'react';
import { connect } from 'react-redux';
import Lang from '../../../../../lang/components/lang';
import PriceDisplay from '../../shared/components/displays/price';
import {
    getAccommodationFromState,
    getAccommodationPrice,
} from './helpers';
import { IAccommodationItem } from './interfaces';

interface IProps {
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
*/
