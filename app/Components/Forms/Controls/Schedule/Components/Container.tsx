import { Params } from 'FKSDB/Components/Forms/Controls/Schedule/ScheduleField';
import { ModelScheduleGroup } from 'FKSDB/Models/ORM/Models/Schedule/modelScheduleGroup';
import * as React from 'react';
import Group from './Group';

interface OwnProps {
    group: ModelScheduleGroup;
    params: Params;
}

export default class Container extends React.Component<OwnProps> {

    public render() {
        const {group, params} = this.props;
        return <div className="schedule-container schedule-container-accommodation ms-3">
            <Group group={group} params={params}/>
        </div>;
    }
}
