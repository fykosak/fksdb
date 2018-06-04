import * as React from 'react';
import { FormSection } from 'redux-form';
import Lang from '../../../../lang/components/lang';
import { IPersonSelector } from '../../../middleware/price';
import Schedule from '../fields/schedule';

export default class ScheduleSection extends React.Component<IPersonSelector, {}> {
    public render() {
        const {type, index} = this.props;

        return <FormSection name={'schedule'}>
            <h3><Lang text={'Schedule'}/></h3>
            <Schedule type={type} index={index}/>
        </FormSection>;
    }
}
