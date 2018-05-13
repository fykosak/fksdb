import * as React from 'react';
import { connect } from 'react-redux';
import {

    formValueSelector,
} from 'redux-form';

import { getPrice } from '../../middleware/price';
import { FORM_NAME } from '../form';
import { accommodationDef } from './index';

interface IProps {
    type: string;
    index: number;
}

interface IState {
    acc?: any;
}

class Accommodation extends React.Component<IProps & IState, {}> {

    public render() {
        const price = getPrice(accommodationDef, this.props.acc);

        return <div>
            <p>Cena ubytovania.</p>
            <p>{price.eur} €</p>
            <p>{price.kc} Kč</p>
        </div>;
    }
}

const mapDispatchToProps = (): IState => {
    return {};
};

const mapStateToProps = (state, ownProps: IProps): IState => {
    const selector = formValueSelector(FORM_NAME);
    const participantsValue = selector(state, ownProps.type);
    if (participantsValue) {
        if (participantsValue.hasOwnProperty(ownProps.index)) {
            if (participantsValue[ownProps.index].hasOwnProperty('accommodation')) {
                return {
                    acc: participantsValue[ownProps.index].accommodation,
                };
            }
        }
    }
    return {};
};

export default connect(mapStateToProps, mapDispatchToProps)(Accommodation);
