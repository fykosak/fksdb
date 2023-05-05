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

class TaskStats extends React.Component<StateProps> {
    static contextType = TranslatorContext;
    public render() {
        const {taskId, availablePoints} = this.props;
        const translator = this.context;
        return (
            <>
                <h2>{translator.getText('Global statistics')}</h2>
                <ChartContainer
                    chart={Progress}
                    chartProps={{availablePoints}}
                    headline={translator.getText('Total solved problem')}
                />

                <h2>{translator.getText('Statistics from single problem')}</h2>
                <Options/>
                <hr/>
                {taskId && <>
                    <ChartContainer
                        chart={Timeline}
                        chartProps={{taskId}}
                        headline={translator.getText('Timeline')}
                    />
                    <hr/>
                    <ChartContainer
                        chart={TimeHistogram}
                        chartProps={{taskId, availablePoints}}
                        headline={translator.getText('Time histogram')}
                    />
                    <hr/>
                    <ChartContainer
                        chart={TimeHistogramLines}
                        chartProps={{taskId, availablePoints}}
                        headline={translator.getText('Time histogram')}
                    />
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
