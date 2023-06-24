import ChartContainer from 'FKSDB/Components/Charts/Core/ChartContainer';
import * as React from 'react';
import { connect } from 'react-redux';
import Options from './Options';
import TimeHistogram from './BarHistogram';
import TimeHistogramLines from './TimeHistogramLinesChart';
import Timeline from './Timeline';
import Progress from './Progress';
import { Store } from 'FKSDB/Components/Game/ResultsAndStatistics/reducers/store';
import { TranslatorContext } from '@translator/LangContext';

interface StateProps {
    taskId: number;
    availablePoints: number[];
}

class TaskStats extends React.Component<StateProps, never> {
    static contextType = TranslatorContext;

    public render() {
        const {taskId, availablePoints} = this.props;
        const translator = this.context;
        return (
            <>
                <div className="panel color-auto">
                    <div className="container">
                        <h2>{translator.getText('Global statistics')}</h2>
                        <ChartContainer
                            chart={Progress}
                            chartProps={{availablePoints}}
                            headline={translator.getText('Total solved problem')}
                        />
                    </div>
                </div>
                <div class="panel color-auto">
                    <div class="container">
                        <h2>{translator.getText('Statistics from single problem')}</h2>
                        <Options/>
                    </div>
                </div>
                {taskId && <>
                    <div className="panel color-auto">
                        <div className="container">
                            <ChartContainer
                                chart={Timeline}
                                chartProps={{taskId}}
                                headline={translator.getText('Timeline')}
                            />
                        </div>
                    </div>
                    <div className="panel color-auto">
                        <div className="container">
                            <ChartContainer
                                chart={TimeHistogram}
                                chartProps={{taskId, availablePoints}}
                                headline={translator.getText('Time histogram')}
                            />
                        </div>
                    </div>
                    <div className="panel color-auto">
                        <div className="container">
                            <ChartContainer
                                chart={TimeHistogramLines}
                                chartProps={{taskId, availablePoints}}
                                headline={translator.getText('Time histogram')}
                            />
                        </div>
                    </div>
                </>}
            </>
        );
    }
}

const mapStateToProps = (state: Store): StateProps => {
    return {
        availablePoints: state.data.availablePoints,
        taskId: state.statistics.taskId,
    };
};

export default connect(mapStateToProps, null)(TaskStats);
