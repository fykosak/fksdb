import * as React from 'react';
import {
    connect,
    Dispatch,
} from 'react-redux';
import { setChartType } from '../../actions';
import { IFyziklaniStatisticsStore } from '../../reducers';

interface IState {
    chartType?: string;

    onChangeChartsType?(subPage: string): void;
}

class Select extends React.Component<IState, {}> {

    public render() {
        const {chartType, onChangeChartsType} = this.props;
        return (
            <>
                <button
                    className={'button ' + ((chartType === 'teams') ? 'active' : '')}
                    onClick={() => onChangeChartsType('teams')}
                >Teams
                </button>
                <button
                    className={'button ' + (chartType === 'task' ? 'active' : '')}
                    onClick={() => onChangeChartsType('task')}
                >Task
                </button>
            </>
        );
    }
}

const mapStateToProps = (state: IFyziklaniStatisticsStore): IState => {
    return {
        chartType: state.statistics.chartType,
    };
};

const mapDispatchToProps = (dispatch: Dispatch<IFyziklaniStatisticsStore>): IState => {
    return {
        onChangeChartsType: (chartsType) => dispatch(setChartType(chartsType)),
    };
};

export default connect(mapStateToProps, mapDispatchToProps)(Select);
