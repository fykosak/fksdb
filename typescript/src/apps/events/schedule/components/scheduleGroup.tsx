import { lang } from '@i18n/i18n';
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
            <label>{this.getLabel()}</label>
            {group.items.map((item, index) => {
                return <ScheduleItem type={this.props.group.scheduleGroupType} item={item} key={index}/>;
            })}
        </div>;
    }

    private getLabel(): string {
        const {group} = this.props;
        switch (group.scheduleGroupType) {
            case 'accommodation':
                return lang.getText('Accommodation from %from% to %to%.')
                    .replace('%from%', (new Date(group.start)).toLocaleDateString(lang.getBCP47()))
                    .replace('%to%', (new Date(group.end)).toLocaleDateString(lang.getBCP47()));
        }
        throw Error();
    }
}
