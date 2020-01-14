import * as React from 'react';
import { ScheduleGroupDef } from '../middleware/interfaces';
import { Params } from './index';
import Group from './group';

interface OwnProps {
    groups: ScheduleGroupDef[];
    params: Params;
}

export default class Container extends React.Component<OwnProps, {}> {

    public render() {
        const {groups, params} = this.props;
        return <div className="schedule-container schedule-container-accommodation ml-3">
            {groups.map((group, index) => {
                return <Group key={index} group={group} params={params}/>;
            })}
        </div>;
    }
}
