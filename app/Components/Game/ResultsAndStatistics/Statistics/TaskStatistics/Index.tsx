import * as React from 'react';
import { connect } from 'react-redux';
import Options from './Options';
import TimeHistogram from './bar-histogram';
import TimeHistogramLines from './histogram-lines';
import Timeline from './timeline';
import Progress from './progress';
import { Store } from 'FKSDB/Components/Game/ResultsAndStatistics/reducers/store';
import { TranslatorContext } from '@translator/LangContext';
import Legend from 'FKSDB/Components/Game/ResultsAndStatistics/Statistics/TeamStatistics/legend';

interface StateProps {
    taskId: number;
    availablePoints: number[];
}

class TaskStats extends React.Component<StateProps, never> {
    static contextType = TranslatorContext;

    public render() {
        const {taskId, availablePoints} = this.props;
        const translator = this.context;
        return <>
            <div className="panel color-auto">
                <div className="container">
                    <h2>{translator.getText('Total solved problems')}</h2>
                    <Progress availablePoints={availablePoints}/>
                    <h3>{translator.getText('Legend')}</h3>
                    <Legend/>
                </div>
            </div>
            <div className="panel color-auto">
                <div className="container">
                    <h2>{translator.getText('Statistics of single problem')}</h2>
                    <Options/>
                </div>
            </div>
            {taskId && <>
                <div className="panel color-auto">
                    <div className="container">
                        <h2>{translator.getText('Timeline')}</h2>
                        <Timeline taskId={taskId}/>
                        <h3>{translator.getText('Legend')}</h3>
                        <Legend/>
                    </div>
                </div>
                <div className="panel color-auto">
                    <div className="container">
                        <h2>{translator.getText('Time histogram')}</h2>
                        <TimeHistogram taskId={taskId} availablePoints={availablePoints}/>
                        <h3>{translator.getText('Legend')}</h3>
                        <Legend/>
                    </div>
                </div>
                <div className="panel color-auto">
                    <div className="container">
                        <h2>{translator.getText('Time histogram')}</h2>
                        <TimeHistogramLines taskId={taskId} availablePoints={availablePoints}/>
                        <h3>{translator.getText('Legend')}</h3>
                        <Legend/>
                    </div>
                </div>
            </>}
        </>;
    }
}

const mapStateToProps = (state: Store): StateProps => {
    return {
        availablePoints: state.data.availablePoints,
        taskId: state.statistics.taskId,
    };
};

export default connect(mapStateToProps, null)(TaskStats);
