import * as React from 'react';
import Progress from './progress';

export default class TaskStats extends React.Component<{}, {}> {
    public render() {
        return (
            <div>
                <h2>Tasks statistics</h2>
                <Progress/>
            </div>
        );
    }
}
