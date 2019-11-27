import { lang } from '@i18n/i18n';
import * as React from 'react';
import { connect } from 'react-redux';
import { Store as StatisticsStore } from '../../../reducers';
import Options from './options/';
import Progress from './progress/';
import TimeHistogram from './timeHistogram/';
import TimeHistogramLines from './timeHistogramLines/';
import Timeline from './timeline/';

interface StateProps {
    taskId: number;
}

class TaskStats extends React.Component<StateProps, {}> {
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

const mapStateToProps = (state: StatisticsStore): StateProps => {
    return {
        taskId: state.statistics.taskId,
    };
};

export default connect(mapStateToProps, null)(TaskStats);
