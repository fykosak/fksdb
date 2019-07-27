import * as React from 'react';
import { connect } from 'react-redux';
import { lang } from '@i18n/i18n';
import { Store as StatisticsStore } from '../../../reducers';
import Options from './options/Index';
import Progress from './progress/Index';
import TimeHistogramLines from './time-histogram-lines/Index';
import TimeHistogram from './time-histogram/Index';
import Timeline from './timeline/Index';

interface State {
    taskId?: number;
}

class TaskStats extends React.Component<State, {}> {
    public render() {
        const {taskId} = this.props;
        const availablePoints = [5, 3, 2, 1];
        return (
            <>
                <h2>{lang.getText('Global statistics')}</h2>
                <Progress availablePoints={availablePoints}/>

                <h2>{lang.getText('Statistics from single problem')}</h2>
                <Options/>
                <hr/>
                {taskId && <>
                    <Timeline taskId={taskId} availablePoints={availablePoints}/>
                    <hr/>
                    <TimeHistogram taskId={taskId} availablePoints={availablePoints}/>
                    <hr/>
                    <TimeHistogramLines taskId={taskId} availablePoints={availablePoints}/>
                </>}
            </>
        );
    }
}

const mapStateToProps = (state: StatisticsStore): State => {
    return {
        taskId: state.statistics.taskId,
    };
};

export default connect(mapStateToProps, null)(TaskStats);
