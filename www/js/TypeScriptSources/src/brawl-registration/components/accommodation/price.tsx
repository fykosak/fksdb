import * as React from 'react';
import { connect } from 'react-redux';
import { IAccommodationItem } from '../../middleware/iterfaces';
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
    accommodationDef?: IAccommodationItem[];
}

class Price extends React.Component<IProps & IState, {}> {

    public render() {
        const price = getPrice(this.props.accommodationDef, this.props.acc);

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
    return {
        accommodationDef: state.definitions.accommodation,
        ...getAccommodationFromState(FORM_NAME, state, ownProps),
    };
};

export default connect(mapStateToProps, mapDispatchToProps)(Price);
