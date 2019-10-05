import * as React from 'react';
import { ScheduleGroupDef } from '../middleware/interfaces';
import ScheduleGroup from './scheduleGroup';
import { Params } from './index';

interface Props {
    groups: ScheduleGroupDef[];
    params: Params;
}

export default class Container extends React.Component<Props, {}> {

    public render() {
        const {groups, params} = this.props;
        return <div className="schedule-container schedule-container-accommodation">
            {groups.map((group, index) => {
                return <ScheduleGroup key={index} group={group} params={params}/>;
            })}
        </div>;
    }
}
