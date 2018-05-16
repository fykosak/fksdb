import * as React from 'react';
import { connect } from 'react-redux';
import {

    formValueSelector,
} from 'redux-form';

import {
    getAccommodationFromState,
    getPrice,
} from '../../middleware/price';
import { FORM_NAME } from '../form';

interface IProps {
    type: string;
    index: number;
}

interface IState {
    acc?: any;
}

class Sumary extends React.Component<IProps & IState, {}> {

    public render() {
        // const price = getPrice(accommodationDef, this.props.acc);
        //   <p>{price.eur} €</p>
        //             <p>{price.kc} Kč</p>

        return <div>
            <p>Cena ubytovania.</p>
        </div>;
    }
}

const mapDispatchToProps = (): IState => {
    return {};
};

const mapStateToProps = (state, ownProps: IProps): IState => {
    return getAccommodationFromState(FORM_NAME, state, ownProps);
};

export default connect(mapStateToProps, mapDispatchToProps)(Sumary);
