import * as React from 'react';
import Lang from '../../../../../lang/components/lang';
import Progress from './progress';

export default class TaskStats extends React.Component<{}, {}> {
    public render() {
        return (
            <div>
                <h2><Lang text={'tasksStatistics'}/></h2>
                <Progress/>
            </div>
        );
    }
}
