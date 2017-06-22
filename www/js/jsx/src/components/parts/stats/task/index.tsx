import * as React from 'react';
import Progress from './progress';

interface IProps {
}

export default class TaskStats extends React.Component<IProps, void> {
    render() {
        return (
            <div>
                <h2>Tasks statistics</h2>
                <Progress/>
            </div>
        );
    }
}
