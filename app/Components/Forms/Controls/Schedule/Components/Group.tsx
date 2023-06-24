import { Params } from 'FKSDB/Components/Forms/Controls/Schedule/ScheduleField';
import { ModelScheduleGroup } from 'FKSDB/Models/ORM/Models/Schedule/modelScheduleGroup';
import TimeDisplay from 'FKSDB/Models/ValuePrinters/DatePrinter';
import DateDisplay from 'FKSDB/Models/ValuePrinters/DatePrinter';
import * as React from 'react';
import ScheduleItem from './Item';
import { TranslatorContext } from '@translator/LangContext';

interface OwnProps {
    group: ModelScheduleGroup;
    params: Params;
}

export default class Group extends React.Component<OwnProps, never> {
    static contextType = TranslatorContext;

    public render() {
        const translator = this.context;
        const {group, params} = this.props;
        return <div className="ms-3">
            <h5 className="mb-3">
                {translator.get(group.name)}
                {params.groupTime && (
                    <small className="ms-3 text-muted">
                        <TimeDisplay date={group.start} translator={translator}/> - <TimeDisplay date={group.end}
                                                                                                 translator={translator}/>
                    </small>)}
            </h5>
            {(group.registrationEnd || group.modificationEnd) &&
                <div className="alert alert-info">
                    {group.registrationEnd && <p>
                        <i className="fas fa-info me-2"/>
                        {translator.getText('Registration end: ')}
                        <DateDisplay date={group.registrationEnd} translator={translator}/>
                    </p>
                    }
                    {group.modificationEnd && group.modificationEnd != group.registrationEnd && <p>
                        <i className="fas fa-info me-2"/>
                        {translator.getText('Modification end: ')}
                        <DateDisplay date={group.modificationEnd} translator={translator}/>
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
