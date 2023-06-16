import { Params } from 'FKSDB/Components/Schedule/Input/ScheduleField';
import { ModelScheduleGroup } from 'FKSDB/Models/ORM/Models/Schedule/modelScheduleGroup';
import TimeDisplay from 'FKSDB/Models/ValuePrinters/DatePrinter';
import DateDisplay from 'FKSDB/Models/ValuePrinters/DatePrinter';
import * as React from 'react';
import ScheduleItem from './Item';
import { translator } from '@translator/translator';

interface OwnProps {
    group: ModelScheduleGroup;
    params: Params;
}

export default class Group extends React.Component<OwnProps> {

    public render() {
        const {group, params} = this.props;
        return <div className="ms-3">
            <h5 className="mb-3">
                {group.name[translator.getCurrentLocale()]}
                {params.groupTime && (
                    <small className="ms-3 text-muted">
                        <TimeDisplay date={group.start}/> - <TimeDisplay date={group.end}/>
                    </small>)}
            </h5>
            {(group.registrationEnd || group.modificationEnd) &&
                <div className="alert alert-info">
                    {group.registrationEnd && <p>
                        <i className="fa fa-info me-2"/>
                        {translator.getText('Registration end: ')}
                        <DateDisplay date={group.registrationEnd}/>
                    </p>
                    }
                    {group.modificationEnd && group.modificationEnd != group.registrationEnd && <p>
                        <i className="fa fa-info me-2"/>
                        {translator.getText('Modification end: ')}
                        <DateDisplay date={group.modificationEnd}/>
                    </p>
                    }
                </div>
            }
            <div className="row row-cols-1 row-cols-md-2 row-cols-lg-3">
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
