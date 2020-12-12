import { Params } from '@FKSDB/Components/Forms/Controls/Schedule/ScheduleField';
import { ModelScheduleGroup } from '@FKSDB/Model/ORM/Models/Schedule/ModelScheduleGroup';
import * as React from 'react';
import Group from './Group';

interface OwnProps {
    groups: ModelScheduleGroup[];
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
