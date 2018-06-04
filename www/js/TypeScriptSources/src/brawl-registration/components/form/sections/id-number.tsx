import * as React from 'react';
import { FormSection } from 'redux-form';
import Lang from '../../../../lang/components/lang';
import { IPersonAccommodation } from '../../../middleware/price';
import BaseInput from '../../inputs/base-input';
import Input from '../../inputs/input';

import { IScheduleItem } from '../../../middleware/iterfaces';

interface IProps {
    type: string;
    index: number;
    accommodation: IPersonAccommodation;
    providerOpt: {
        idNumber: { hasValue: boolean; value: string };
    };
    schedule: boolean[];
    scheduleDef: IScheduleItem[];
}

export default class IdNumberSection extends React.Component<IProps, {}> {
    public render() {
        const {providerOpt: {idNumber}, accommodation, schedule, scheduleDef} = this.props;
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
