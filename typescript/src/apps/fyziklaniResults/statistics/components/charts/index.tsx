import * as React from 'react';
import Timer from '../../../timer/components';
import CorrelationStats from './correlation';
import TasksStats from './task';
import TeamStats from './team';

interface OwnProps {
    mode: 'correlation' | 'team' | 'task';
}

export default class Statistics extends React.Component<OwnProps, {}> {

    public render() {
        let content = null;
        const {mode} = this.props;
        switch (mode) {
            case 'team':
            default:
                content = (<TeamStats/>);
                break;
            case 'task':
                content = (<TasksStats/>);
                break;
            case 'correlation':
                content = (<CorrelationStats/>);
        }
        return (
            <div className="container">
                {content}
                <Timer mode={'small'}/>
            </div>
        );
    }
}
