import * as React from 'react';
import { ScheduleGroupDef } from '../middleware/interfaces';
import ScheduleGroup from './scheduleGroup';

interface Props {
    groups: ScheduleGroupDef[];
}

export default class Container extends React.Component<Props, {}> {

    public render() {
        const {groups} = this.props;
        return <div className="schedule-container schedule-container-accommodation">
            {groups.map((group, index) => {
                return <ScheduleGroup key={index} group={group}/>;
            })}
        </div>;
    }
}
