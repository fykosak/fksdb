import * as React from 'react';
import { connect } from 'react-redux';
import { FORM_NAME } from '../../';
import Lang from '../../../../../lang/components/lang';
import {
    IScheduleItem,
} from '../../../../middleware/iterfaces';
import {
    getScheduleFromState,
    getSchedulePrice,
    IPersonStringSelectror,
} from '../../../../middleware/price';
import PriceDisplay from '../../../displays/price';

interface IProps {
    type: string;
    index: number;
}

interface IState {
    schedule?: boolean[];
    scheduleDef?: IScheduleItem[];
}

class Price extends React.Component<IPersonStringSelectror & IState, {}> {

    public render() {
        const price = getSchedulePrice(this.props.scheduleDef, this.props.schedule);

        return <div>
            <p><Lang text={'Cena za sprievodné akcie.'}/></p>
            <PriceDisplay eur={price.eur} kc={price.kc}/>
        </div>;
    }
}

const mapDispatchToProps = (): IState => {
    return {};
};

const mapStateToProps = (state, ownProps: IProps): IState => {
    return {
        schedule: getScheduleFromState(FORM_NAME, state, ownProps),
        scheduleDef: state.definitions.schedule,
    };
};

export default connect(mapStateToProps, mapDispatchToProps)(Price);
