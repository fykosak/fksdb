import * as React from 'react';
import { IPersonSelector } from '../../../middleware/price';
import Price from '../fields/price';
import AccommodationGroup from '../sections/accommodation';
import BaseInfoGroup from '../sections/base-info';
import IdNumberSection from '../sections/id-number';
import ScheduleGroup from '../sections/schedule';
import SchoolGroup from '../sections/school';
import { getAccessKey } from '../../../../person-provider/validation';

export default class ParticipantForm extends React.Component<IPersonSelector, {}> {
    public render() {
        const {type, index} = this.props;
        const accessKey = getAccessKey(type,index);
        return <>
            <BaseInfoGroup accessKey={accessKey}/>
            <SchoolGroup accessKey={accessKey}/>
            <AccommodationGroup accessKey={accessKey}/>
            <ScheduleGroup type={type} index={index}/>
            <IdNumberSection type={type} index={index}/>
            <Price type={type} index={index}/>
        </>;
    }
}
