import * as React from 'react';
import { FormSection } from 'redux-form';
import Lang from '../../../../lang/components/lang';
import { IPersonSelector } from '../../../middleware/price';
import Schedule from '../fields/schedule';

interface IProps {
    personSelector: IPersonSelector;
}

export default class ScheduleSection extends React.Component<IProps, {}> {
    public render() {
        const {personSelector} = this.props;

        return <FormSection name={'schedule'}>
            <h3><Lang text={'Schedule'}/></h3>
            <Schedule personSelector={personSelector}/>
        </FormSection>;
    }
}
