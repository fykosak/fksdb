import * as React from 'react';
import { connect } from 'react-redux';
import { Dispatch } from 'redux';
import {
    Field,
    FormSection,
} from 'redux-form';
import Lang from '../../../../lang/components/lang';
import { clearProviderProviderProperty } from '../../../../person-provider/actions';
import InputProvider from '../../../../person-provider/components/input-provider';
import { IScheduleItem } from '../../../middleware/iterfaces';
import { getFieldName } from '../../../middleware/person';
import {
    getAccommodationFromState,
    getScheduleFromState,
    IPersonAccommodation,
    IPersonSelector,
} from '../../../middleware/price';
import { IStore } from '../../../reducers';
import BaseInput from '../../inputs/base-input';
import { FORM_NAME } from '../index';

interface IState {
    accommodation?: IPersonAccommodation;
    schedule?: boolean[];
    scheduleDef?: IScheduleItem[];
    removeIdNumberValue?: () => void;
}

class IdNumberSection extends React.Component<IPersonSelector & IState, {}> {
    public render() {
        const {accommodation, schedule, scheduleDef, removeIdNumberValue} = this.props;
        let requireIdNumber = false;
        const requiredValues = [];
        for (const date in accommodation) {
            if (accommodation.hasOwnProperty(date)) {
                requireIdNumber = requireIdNumber || !!accommodation[date];
            }
        }
        if (requireIdNumber) {
            requiredValues.push(<li className="text-muted" key={'acc'}><Lang text={'Accommodation'}/></li>);
        }
        if (schedule) {
            schedule.forEach((scheduleValue, scheduleIndex) => {
                if (!scheduleValue) {
                    return;
                }
                const selectedSchedule = scheduleDef.filter((value) => {
                    return value.id === scheduleIndex;
                })[0];

                if (selectedSchedule && selectedSchedule.requireIdNumber) {
                    requireIdNumber = true;
                    requiredValues.push(<li className="text-muted" key={scheduleIndex}>{selectedSchedule.scheduleName}</li>);
                }
            });
        }
        if (!requireIdNumber) {
            return null;
        }
        // description={<Lang text={'Kvôli ubytovaniu.'}/>}

        return <FormSection name={'baseInfo'}>
            <h3><Lang text={'Číslo OP/Pasu'}/></h3>
            <Field
                accessKey={getFieldName(this.props.type, this.props.index)}
                name={'idNumber'}
                component={InputProvider}
                JSXLabel={<Lang text={'Číslo OP/pasu'}/>}
                type={'text'}
                secure={true}
                providerInput={BaseInput}
                modifiable={true}
                required={true}
            />
            <h6><Lang text={'Akcie vyžadujúve OP'}/></h6>
            <ul>
                {requiredValues}
            </ul>
        </FormSection>;
    }
}

const mapDispatchToProps = (dispatch: Dispatch<IStore>, ownProps: IPersonSelector): IState => {
    const accessKey = getFieldName(ownProps.type, ownProps.index);
    return {
        removeIdNumberValue: () => dispatch(clearProviderProviderProperty(accessKey, 'idNumber')),
    };
};

const mapStateToProps = (state: IStore, ownProps: IPersonSelector): IState => {
    const accessKey = getFieldName(ownProps.type, ownProps.index);
    if (state.provider.hasOwnProperty(accessKey)) {
        // const fieldNames = ['personId', 'email', 'school', 'studyYear', 'accommodation', 'idNumber', 'familyName', 'otherName'];
        return {
            accommodation: getAccommodationFromState(FORM_NAME, state, ownProps),
            schedule: getScheduleFromState(FORM_NAME, state, ownProps),
            scheduleDef: state.definitions.schedule,
        };
    }
    return {};
};

export default connect(mapStateToProps, mapDispatchToProps)(IdNumberSection);
