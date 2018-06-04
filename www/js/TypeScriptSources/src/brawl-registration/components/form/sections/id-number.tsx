import * as React from 'react';
import { connect } from 'react-redux';
import { FormSection } from 'redux-form';
import Lang from '../../../../lang/components/lang';
import { IProviderValue } from '../../../../person-provider/interfaces';
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
import Input from '../../inputs/input';
import { FORM_NAME } from '../index';

interface IState {
    accommodation?: IPersonAccommodation;
    idNumber?: IProviderValue<string>;
    schedule?: boolean[];
    scheduleDef?: IScheduleItem[];
}

class IdNumberSection extends React.Component<IPersonSelector & IState, {}> {
    public render() {
        const {idNumber, accommodation, schedule, scheduleDef} = this.props;
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
            <Input label={<Lang text={'Číslo OP/pasu'}/>}
                   type={'text'}
                   secure={true}
                   component={BaseInput}
                   modifiable={true}
                   name={'idNumber'}
                   providerOptions={idNumber}
                   required={true}
            />
            <h6><Lang text={'Akcie vyžadujúve OP'}/></h6>
            <ul>
                {requiredValues}
            </ul>
        </FormSection>;
    }
}

const mapDispatchToProps = (): IState => {
    return {};
};

const mapStateToProps = (state: IStore, ownProps: IPersonSelector): IState => {
    const accessKey = getFieldName(ownProps.type, ownProps.index);
    if (state.provider.hasOwnProperty(accessKey)) {
        // const fieldNames = ['personId', 'email', 'school', 'studyYear', 'accommodation', 'idNumber', 'familyName', 'otherName'];
        return {
            accommodation: getAccommodationFromState(FORM_NAME, state, ownProps),
            idNumber: state.provider[accessKey].fields.idNumber,
            schedule: getScheduleFromState(FORM_NAME, state, ownProps),
            scheduleDef: state.definitions.schedule,
        };
    }
    return {};
};

export default connect(mapStateToProps, mapDispatchToProps)(IdNumberSection);
