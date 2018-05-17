import * as React from 'react';
import { connect } from 'react-redux';
import {
    IScheduleItem,
} from '../../middleware/iterfaces';
import {
    getScheduleFromState,
    getSchedulePrice,
} from '../../middleware/price';
import { FORM_NAME } from '../form';
import PriceDisplay from '../displays/price';

interface IProps {
    type: string;
    index: number;
}

interface IState {
    schedule?: any;
    scheduleDef?: IScheduleItem[];
}

class Price extends React.Component<IProps & IState, {}> {

    public render() {
        const price = getSchedulePrice(this.props.scheduleDef, this.props.schedule);

        return <div>
            <p>Cena za sprievodné akcie.</p>
            <PriceDisplay eur={price.eur} kc={price.kc}/>
        </div>;
    }
}

const mapDispatchToProps = (): IState => {
    return {};
};

const mapStateToProps = (state, ownProps: IProps): IState => {
    return {
        scheduleDef: state.definitions.schedule,
        ...getScheduleFromState(FORM_NAME, state, ownProps),
    };
};

export default connect(mapStateToProps, mapDispatchToProps)(Price);
