import * as React from 'react';
import Timer from '../../../helpers/components/timer/';
import CorrelationStats from './correlation/';
import TasksStats from './task/';
import TeamStats from './team/';

interface Props {
    mode: string;
}

export default class Statistics extends React.Component<Props, {}> {

    public render() {
        let content = null;
        const {mode} = this.props;
        switch (mode) {
            case 'teams':
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
