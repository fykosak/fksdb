import * as React from 'react';
import { ScheduleGroupDef } from '../middleware/interfaces';
import ScheduleItem from './scheduleItem';

interface Props {
    group: ScheduleGroupDef;
}

export default class ScheduleGroup extends React.Component<Props, {}> {

    public render() {
        const {group} = this.props;
        return <div className="schedule-container schedule-container-accommodation">
            <label>{group.label}</label>
            {group.items.map((item, index) => {
                return <ScheduleItem type={this.props.group.scheduleGroupType} item={item} key={index}/>;
            })}
        </div>;
    }
}
