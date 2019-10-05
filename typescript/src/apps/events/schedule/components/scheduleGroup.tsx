import * as React from 'react';
import { ScheduleGroupDef } from '../middleware/interfaces';
import ScheduleItem from './scheduleItem';
import { Params } from './index';

interface Props {
    group: ScheduleGroupDef;
    params: Params;
}

export default class ScheduleGroup extends React.Component<Props, {}> {

    public render() {
        const {group, params} = this.props;
        return <div className="schedule-container schedule-container-accommodation">
            {params.displayGroupLabel && (<label>{group.label}</label>)}
            {group.items.map((item, index) => {
                return <ScheduleItem params={params}
                                     type={this.props.group.scheduleGroupType}
                                     item={item}
                                     key={index}/>;
            })}
        </div>;
    }
}
