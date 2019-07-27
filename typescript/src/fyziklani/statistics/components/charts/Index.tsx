import * as React from 'react';
import Timer from '../../../helpers/components/timer/Index';
import CorrelationStats from './correlation/Index';
import TasksStats from './task/Index';
import TeamStats from './team/Index';

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
