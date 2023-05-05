import { TaskModel } from 'FKSDB/Models/ORM/Models/Fyziklani/TaskModel';
import TimeDisplay from 'FKSDB/Models/ValuePrinters/TimePrinter';
import * as React from 'react';
import { connect } from 'react-redux';
import { Action, Dispatch } from 'redux';
import { setNewState } from '../../actions/stats';
import { Store } from 'FKSDB/Components/Game/ResultsAndStatistics/reducers/store';
import { State } from 'FKSDB/Components/Game/ResultsAndStatistics/reducers/stats';
import { TranslatorContext } from '@translator/LangContext';

interface StateProps {
    aggregationTime: number;
    tasks: TaskModel[];
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
    static contextType = TranslatorContext;
    public render() {
        const translator = this.context;
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

                <div className="row">
                    <div className="col-6">
                        <div className="form-group">
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
                    <div className="col-6">
                        <div className="form-group">
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
                <div className="row">
                    <div className="col-6">
                        <div className="form-group">
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
                            <span className="form-text"><TimeDisplay date={fromDate.toISOString()}
                                                                     translator={translator}/></span>
                        </div>
                    </div>
                    <div className="col-6">
                        <div className="form-group">
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
                            <span className="form-text"><TimeDisplay date={toDate.toISOString()}
                                                                     translator={translator}/></span>
                        </div>
                    </div>
                </div>
            </>
        );
    }
}

const mapStateToProps = (state: Store): StateProps => {
    return {
        aggregationTime: state.statistics.aggregationTime,
        fromDate: state.timer.gameStart,
        gameEnd: state.timer.gameEnd,
        gameStart: state.timer.gameStart,
        taskId: state.statistics.taskId,
        tasks: state.data.tasks,
        toDate: state.timer.gameEnd,
    };
};

const mapDispatchToProps = (dispatch: Dispatch<Action<string>>): DispatchProps => {
    return {
        onSetNewState: (data) => dispatch(setNewState(data)),
    };
};

export default connect(mapStateToProps, mapDispatchToProps)(Options);
