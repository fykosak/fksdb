import { Params } from '@FKSDB/Components/Forms/Controls/Schedule/ScheduleField';
import { ModelScheduleGroup } from '@FKSDB/Model/ORM/Models/Schedule/modelScheduleGroup';
import TimeDisplay from '@FKSDB/Model/ValuePrinters/DatePrinter';
import { translator } from '@translator/translator';
import * as React from 'react';
import ScheduleItem from './Item';

interface OwnProps {
    group: ModelScheduleGroup;
    params: Params;
}

export default class Group extends React.Component<OwnProps, {}> {

    public render() {
        const {group, params} = this.props;
        return <div className="schedule-container schedule-container-accommodation">
            {params.display.groupLabel && (<label>{group.label[translator.getCurrentLocale()]}</label>)}
            {params.display.groupTime && (
                <small className="ml-3">
                    <TimeDisplay date={group.start}/>-<TimeDisplay date={group.end}/>
                </small>)}
            <div className="row">
                {group.items.map((item, index) => {
                    return <div key={index} className="col">
                        <ScheduleItem
                            params={params}
                            type={this.props.group.scheduleGroupType}
                            item={item}
                        />
                    </div>;
                })}
            </div>
        </div>;
    }
}
