import ChartContainer from '@FKSDB/Components/Controls/Chart/Core/ChartContainer';
import { translator } from '@translator/translator';
import * as React from 'react';
import { connect } from 'react-redux';
import { Store as StatisticsStore } from '../Reducers';
import Options from './Options';
import TimeHistogram from './TimeHistogramBarsChart';
import TimeHistogramLines from './TimeHistogramLinesChart';
import Timeline from './Timeline';
import Progress from './TimeProgress';

interface StateProps {
    taskId: number;
}

class TaskStats extends React.Component<StateProps, {}> {
    public render() {
        const {taskId} = this.props;
        const availablePoints = [5, 3, 2, 1];
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

const mapStateToProps = (state: StatisticsStore): StateProps => {
    return {
        taskId: state.statistics.taskId,
    };
};

export default connect(mapStateToProps, null)(TaskStats);
