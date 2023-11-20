import { Params } from 'FKSDB/Components/Schedule/Input/schedule-field';
import { ScheduleGroupModel } from 'FKSDB/Models/ORM/Models/Schedule/schedule-group-model';
import TimePrinter from 'FKSDB/Models/UI/time-printer';
import DateDisplay from 'FKSDB/Models/UI/date-printer';
import * as React from 'react';
import { useContext } from 'react';
import ScheduleItem from './item';
import { TranslatorContext } from '@translator/context';

interface OwnProps {
    group: ScheduleGroupModel;
    params: Params;
}

export default function Group({group, params}: OwnProps) {
    const translator = useContext(TranslatorContext);
    return <div className="ms-3">
        <h5 className="mb-3">
            {translator.get(group.name)}
            {params.groupTime && (
                <small className="ms-3 text-muted">
                    <TimePrinter
                        date={group.start}
                        translator={translator}
                    /> - <TimePrinter
                    date={group.end}
                    translator={translator}
                />
                </small>)}
        </h5>
        {group.registrationEnd && <p className="alert alert-info">
            <i className="fas fa-info me-2"/>
            {translator.getText('Registration end: ')}
            <DateDisplay date={group.registrationEnd} translator={translator}/>
        </p>
        }
        <div className="row row-cols-1 row-cols-md-2 row-cols-lg-3">
            {group.items.map((item, index) => {
                return <div key={index} className="col">
                    <ScheduleItem
                        params={params}
                        type={group.scheduleGroupType}
                        item={item}
                        />
                    </div>;
                })}
            </div>
        </div>;
}
