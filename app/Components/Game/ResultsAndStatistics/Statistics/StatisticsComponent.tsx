import { ResponseData } from 'FKSDB/Components/Game/ResultsAndStatistics/Helpers/Downloader/Downloader';
import MainComponent from 'FKSDB/Components/Game/ResultsAndStatistics/Helpers/MainComponent';
import * as React from 'react';
import { app } from '../reducers/store';
import { NetteActions } from 'vendor/fykosak/nette-frontend-component/src/NetteActions/netteActions';
import TeamStats from 'FKSDB/Components/Game/ResultsAndStatistics/Statistics/TeamStatistics/Index';
import TasksStats from 'FKSDB/Components/Game/ResultsAndStatistics/Statistics/TaskStatistics/Index';
import CorrelationStats
    from 'FKSDB/Components/Game/ResultsAndStatistics/Statistics/CorrelationStatitics/Index';

interface OwnProps {
    mode: 'correlation' | 'team' | 'task';
    actions: NetteActions;
    data: ResponseData;
}

export default class StatisticsComponent extends React.Component<OwnProps> {
    public render() {
        const {mode} = this.props;
        let content = null;
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
        return <MainComponent app={app} data={this.props.data} actions={this.props.actions}>
            <div className="container">
                {content}
            </div>
        </MainComponent>;
    }
}
