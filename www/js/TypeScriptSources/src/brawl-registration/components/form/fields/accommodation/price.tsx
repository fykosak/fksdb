import * as React from 'react';
import { connect } from 'react-redux';
import Lang from '../../../../../lang/components/lang';
import { IAccommodationItem } from '../../../../middleware/iterfaces';
import {
    getAccommodationFromState,
    getAccommodationPrice,
} from '../../../../middleware/price';
import PriceDisplay from '../../../displays/price';
import { FORM_NAME } from '../../index';

interface IProps {
    type: string;
    index: number;
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
            <PriceDisplay eur={price.eur} kc={price.kc}/>
        </>;
    }
}

const mapDispatchToProps = (): IState => {
    return {};
};

const mapStateToProps = (state, ownProps: IProps): IState => {
    return {
        accommodation: getAccommodationFromState(FORM_NAME, state, ownProps),
        accommodationDef: state.definitions.accommodation,
    };
};

export default connect(mapStateToProps, mapDispatchToProps)(Price);
