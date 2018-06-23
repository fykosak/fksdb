import * as React from 'react';
import { connect } from 'react-redux';
import Lang from '../../../../lang/components/lang';
import IdNumber from '../../../../person-provider/components/fields/person-info/id-number';
import { IScheduleItem } from '../../../middleware/iterfaces';
import { getFieldName } from '../../../middleware/person';
import {
    getAccommodationFromState,
    getScheduleFromState,
    IPersonAccommodation,
    IPersonSelector,
    requireIdNumberFromAccommodation,
    requireIdNumberFromSchedule,
} from '../../../middleware/price';
import { IStore } from '../../../reducers';
import { FORM_NAME } from '../index';

interface IState {
    accommodation?: IPersonAccommodation;
    schedule?: boolean[];
    scheduleDef?: IScheduleItem[];
}

interface IProps {
    accessKey: string;
}

class IdNumberSection extends React.Component<IProps & IState, {}> {
    public render() {
        const {accommodation, schedule, scheduleDef, accessKey} = this.props;
        const requiredValues = requireIdNumberFromAccommodation(accommodation).concat(requireIdNumberFromSchedule(schedule, scheduleDef));

        if (requiredValues.length === 0) {
            return null;
        }
        return <div>
            <h3><Lang text={'Číslo OP/Pasu'}/></h3>
            <IdNumber accessKey={accessKey}/>
            <h6><Lang text={'Akcie vyžadujúve OP'}/></h6>
            <ul>
                {requiredValues.map((value, index) => {
                    return <li className="text-muted" key={index}><Lang text={value}/></li>;
                })}
            </ul>
        </div>;
    }
}

const mapDispatchToProps = (): IState => {
    return {};
};

const mapStateToProps = (state: IStore, ownProps: IPersonSelector): IState => {
    const accessKey = getFieldName(ownProps.type, ownProps.index);
    if (state.provider.hasOwnProperty(accessKey)) {
        return {
            accommodation: getAccommodationFromState(FORM_NAME, state, ownProps),
            schedule: getScheduleFromState(FORM_NAME, state, ownProps),
            scheduleDef: state.definitions.schedule,
        };
    }
    return {};
};

export default connect(mapStateToProps, mapDispatchToProps)(IdNumberSection);
