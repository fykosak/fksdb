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
    acc?: any;
    accommodationDef?: IAccommodationItem[];
}

class Price extends React.Component<IProps & IState, {}> {

    public render() {
        const price = getAccommodationPrice(this.props.accommodationDef, this.props.acc);

        return <div>
            <p><Lang text={'Accommodation price'}/></p>
            <PriceDisplay eur={price.eur} kc={price.kc}/>
        </div>;
    }
}

const mapDispatchToProps = (): IState => {
    return {};
};

const mapStateToProps = (state, ownProps: IProps): IState => {
    return {
        accommodationDef: state.definitions.accommodation,
        ...getAccommodationFromState(FORM_NAME, state, ownProps),
    };
};

export default connect(mapStateToProps, mapDispatchToProps)(Price);
