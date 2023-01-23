import { Params } from 'FKSDB/Components/Forms/Controls/Schedule/ScheduleField';
import { ModelScheduleGroup } from 'FKSDB/Models/ORM/Models/Schedule/modelScheduleGroup';
import TimeDisplay from 'FKSDB/Models/ValuePrinters/DatePrinter';
import * as React from 'react';
import ScheduleItem from './Item';

interface OwnProps {
    group: ModelScheduleGroup;
    params: Params;
}

export default class Group extends React.Component<OwnProps> {

    public render() {
        const {group, params} = this.props;
        return <div className="schedule-container schedule-container-accommodation">
            {params.groupTime && (
                <small className="ms-3 text-muted">
                    <TimeDisplay date={group.start}/>-<TimeDisplay date={group.end}/>
                </small>)}
            <div className="row">
                {group.items.map((item, index) => {
                    return <div key={index}
                                className={`col-12 ${(group.items.length < 3) ? 'col-xl-6' : 'col-12 col-sm-6 col-md-4 col-xl-2'}`}>
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
