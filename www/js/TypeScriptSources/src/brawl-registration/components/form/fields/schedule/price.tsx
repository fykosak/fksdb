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
    IPersonSelector,
} from '../../../../middleware/price';
import PriceDisplay from '../../../displays/price';

interface IProps {
    personSelector: IPersonSelector;
}

interface IState {
    schedule?: boolean[];
    scheduleDef?: IScheduleItem[];
}

class Price extends React.Component<IProps & IState, {}> {

    public render() {
        const price = getSchedulePrice(this.props.scheduleDef, this.props.schedule);

        return <div>
            <p><Lang text={'Cena za sprievodné akcie.'}/></p>
            <PriceDisplay price={price}/>
        </div>;
    }
}

const mapDispatchToProps = (): IState => {
    return {};
};

const mapStateToProps = (state, ownProps: IProps): IState => {
    return {
        schedule: getScheduleFromState(FORM_NAME, state, ownProps.personSelector),
        scheduleDef: state.definitions.schedule,
    };
};

export default connect(mapStateToProps, mapDispatchToProps)(Price);
