import * as React from 'react';
import { FormSection } from 'redux-form';
import Schedule from '../fields/schedule';
import Lang from '../../../../lang/components/lang';

interface IProps {
    type: string;
    index: number;
    providerOpt: {};
}

export default class ScheduleGroup extends React.Component<IProps, {}> {
    public render() {
        const {type, index} = this.props;

        return <FormSection name={'schedule'}>
            <h3><Lang text={'Schedule'}/></h3>
            <Schedule type={type} index={index}/>
        </FormSection>;
    }
}
