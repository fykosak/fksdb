import { translator } from '@translator/translator';
import { ModelFyziklaniTask } from 'FKSDB/Models/ORM/Models/Fyziklani/modelFyziklaniTask';
import TimeDisplay from 'FKSDB/Models/ValuePrinters/TimePrinter';
import * as React from 'react';
import { connect } from 'react-redux';
import {
    Action,
    Dispatch,
} from 'redux';
import { setNewState } from '../actions';
import { Store as StatisticsStore } from '../Reducers';
import { State } from 'FKSDB/Components/Controls/Fyziklani/ResultsAndStatistics/Statistics/Reducers/stats';

interface StateProps {
    aggregationTime: number;
    tasks: ModelFyziklaniTask[];
    taskId: number;
    fromDate: Date;
    toDate: Date;
    gameStart: Date;
    gameEnd: Date;
}

interface DispatchProps {
    onSetNewState(data: State): void;
}

class Options extends React.Component<StateProps & DispatchProps> {

    public componentDidMount() {
        const {onSetNewState, gameEnd, gameStart} = this.props;
        onSetNewState({fromDate: gameStart});
        onSetNewState({toDate: gameEnd});
    }

    public render() {
        const {
            aggregationTime,
            onSetNewState,
            tasks,
            taskId,
            gameStart,
            gameEnd,
            fromDate,
            toDate,
        } = this.props;

        if (!toDate || !fromDate) {
            return null;
        }
        return (
            <>
                <h3>{translator.getText('Options')}</h3>

                <div className={'row'}>
                    <div className={'col-6'}>
                        <div className={'form-group'}>
                            <label>Task</label>
                            <select value={taskId} className="form-control" onChange={(event) => {
                                onSetNewState({taskId: +event.target.value})
                            }}>
                                <option value={null}>--{translator.getText('select task')}--</option>
                                {tasks.map((task) => {
                                    return (<option key={task.taskId} value={task.taskId}>{task.label}</option>);
                                })}
                            </select>
                        </div>
                    </div>
                    <div className={'col-6'}>
                        <div className={'form-group'}>
                            <label>{translator.getText('Aggregation time')}</label>
                            <input type="range" max={30 * 60 * 1000} min={60 * 1000}
                                   value={aggregationTime}
                                   step={60 * 1000}
                                   className="form-range"
                                   onChange={(e) => {
                                       onSetNewState({aggregationTime: +e.target.value});
                                   }}/>
                            <span className="form-text">{aggregationTime / (60 * 1000)} min</span>
                        </div>
                    </div>
                </div>
                <div className={'row'}>
                    <div className={'col-6'}>
                        <div className={'form-group'}>
                            <label>{translator.getText('From')}</label>
                            <input type="range"
                                   className="form-range"
                                   value={fromDate.getTime()}
                                   min={gameStart.getTime()}
                                   max={toDate.getTime()}
                                   step={60 * 1000}
                                   onChange={(e) => {
                                       onSetNewState({fromDate: new Date(+e.target.value)});
                                   }}/>
                            <span className={'form-text'}><TimeDisplay date={fromDate.toISOString()}/></span>
                        </div>
                    </div>
                    <div className={'col-6'}>
                        <div className={'form-group'}>
                            <label>{translator.getText('To')}</label>
                            <input type="range"
                                   className="form-range"
                                   value={toDate.getTime()}
                                   min={fromDate.getTime()}
                                   max={gameEnd.getTime()}
                                   step={60 * 1000}
                                   onChange={(e) => {
                                       onSetNewState({toDate: new Date(+e.target.value)})
                                   }}/>
                            <span className={'form-text'}><TimeDisplay date={toDate.toISOString()}/></span>
                        </div>
                    </div>
                </div>
            </>
        );
    }
}

const mapStateToProps = (state: StatisticsStore): StateProps => {
    return {
        aggregationTime: state.statistics.aggregationTime,
        fromDate: state.statistics.fromDate,
        gameEnd: new Date(state.timer.gameEnd),
        gameStart: new Date(state.timer.gameStart),
        taskId: state.statistics.taskId,
        tasks: state.data.tasks,
        toDate: state.statistics.toDate,
    };
};

const mapDispatchToProps = (dispatch: Dispatch<Action<string>>): DispatchProps => {
    return {
        onSetNewState: (data) => dispatch(setNewState(data)),
    };
};

export default connect(mapStateToProps, mapDispatchToProps)(Options);
