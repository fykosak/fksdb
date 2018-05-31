import * as React from 'react';
import { FormSection } from 'redux-form';
import Schedule from '../sections/schedule';

interface IProps {
    type: string;
    index: number;
    providerOpt: {};
}

export default class ScheduleGroup extends React.Component<IProps, {}> {
    public render() {
        const {type, index} = this.props;

        return <div>
            <h3>Schedule</h3>
            <FormSection name={'schedule'}>
                <Schedule type={type} index={index}/>
            </FormSection>
        </div>;
    }
}
