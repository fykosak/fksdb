import * as React from 'react';
import { ScheduleGroupDef } from '../middleware/interfaces';
import ScheduleGroup from './scheduleGroup';

interface Props {
    scheduleDef: ScheduleGroupDef[];
}

export default class Accommodation extends React.Component<Props, {}> {

    public render() {
        const {scheduleDef} = this.props;
        return <div className="schedule-container schedule-container-accommodation">
            {scheduleDef.map((group, index) => {
                return <ScheduleGroup key={index} group={group} type={'accommodation'}/>;
            })}
        </div>;
    }
}
