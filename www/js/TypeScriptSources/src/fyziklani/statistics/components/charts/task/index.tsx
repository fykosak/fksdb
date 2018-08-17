import * as React from 'react';
import { lang } from '../../../../../i18n/i18n';
import Progress from './progress/index';

export default class TaskStats extends React.Component<{}, {}> {
    public render() {
        return (
            <div>
                <h2>{lang.getText('tasksStatistics')}</h2>
                <Progress/>
            </div>
        );
    }
}
