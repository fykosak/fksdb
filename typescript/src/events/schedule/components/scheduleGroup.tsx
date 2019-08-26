import * as React from 'react';
import { lang } from '../../../i18n/i18n';
import { ScheduleGroupDef } from '../middleware/interfaces';

interface Props {
    group: ScheduleGroupDef;
    type: 'accommodation';
}

export default class ScheduleGroup extends React.Component<Props, {}> {

    public render() {
        const {group} = this.props;
        return <div className="schedule-container schedule-container-accommodation">
            <h3>{this.getLabel()}</h3>
        </div>;
    }

    private getLabel() {
        const {type, group} = this.props;
        switch (type) {
            case 'accommodation':
                return lang.getText('accommodation from %from% to %to%.')
                    .replace('%from%', (new Date(group.start)).toLocaleDateString(lang.getBCP47()))
                    .replace('%to%', (new Date(group.end)).toLocaleDateString(lang.getBCP47()));
        }
        throw Error();
    }
}
