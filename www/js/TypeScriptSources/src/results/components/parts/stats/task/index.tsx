import * as React from 'react';
import { lang } from '../../../../lang/index';
import Progress from './progress';

export default class TaskStats extends React.Component<{}, {}> {
    public render() {
        return (
            <div>
                <h2>{lang.getLang('tasksStatistics')}</h2>
                <Progress/>
            </div>
        );
    }
}
