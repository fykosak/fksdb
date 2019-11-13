import * as React from 'react';
import { ScheduleGroupDef } from '../middleware/interfaces';
import { Params } from './index';
import ScheduleItem from './scheduleItem';

interface OwnProps {
    group: ScheduleGroupDef;
    params: Params;
}

export default class ScheduleGroup extends React.Component<OwnProps, {}> {

    public render() {
        const {group, params} = this.props;
        return <div className="schedule-container schedule-container-accommodation">
            {params.display.groupLabel && (<label>{group.label}</label>)}
            {group.items.map((item, index) => {
                return <ScheduleItem
                    params={params}
                    type={this.props.group.scheduleGroupType}
                    item={item}
                    key={index}
                />;
            })}
        </div>;
    }
}
