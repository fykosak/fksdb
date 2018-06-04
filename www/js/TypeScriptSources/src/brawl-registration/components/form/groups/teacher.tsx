import * as React from 'react';
import { IPersonSelector } from '../../../middleware/price';
import Price from '../fields/price';
import AccommodationGroup from '../sections/accommodation';
import BaseInfoGroup from '../sections/base-info';
import IdNumberSection from '../sections/id-number';
import ScheduleGroup from '../sections/schedule';

export default class TeacherForm extends React.Component<IPersonSelector, {}> {
    public render() {
        const {type, index} = this.props;
        return <>
            <BaseInfoGroup type={type} index={index}/>
            <AccommodationGroup type={type} index={index}/>
            <ScheduleGroup type={type} index={index}/>
            <IdNumberSection type={type} index={index}/>
            <Price type={type} index={index}/>
        </>;
    }
}
