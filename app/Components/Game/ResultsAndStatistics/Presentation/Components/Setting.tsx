import { translator } from '@translator/translator';
import * as React from 'react';
import { connect } from 'react-redux';
import { Action, Dispatch } from 'redux';
import {
    ACTION_SET_PARAMS,
    Params,
} from '../../actions/presentation';
import { Store } from 'FKSDB/Components/Game/ResultsAndStatistics/reducers/store';

interface StateProps {
    isOrg: boolean;
    delay: number;
    rows: number;
    cols: number;
    hardVisible: boolean;
}

interface DispatchProps {
    onSetParams(data: Params): void;
}

class Setting extends React.Component<StateProps & DispatchProps, { show: boolean }> {
    constructor(props) {
        super(props);
        this.state = {show: false};
    }

    public render() {
        const {isOrg, onSetParams, cols, delay, rows, hardVisible} = this.props;
        // TODO FUCK BOOTSTRAP!!!!!!!!!!!!!!!!!!!!!!
        //                data-bs-toggle="modal"
        //                 data-bs-target="#fyziklaniPresentationModal"
        return <>
            <div className="fixed-bottom float-start" style={{zIndex: 10001}}>
                <button
                    type="button"
                    className="btn btn-link"
                    onClick={() => this.setState({show: !this.state.show})}
                >
                    <i className="fa fa-cogs"/>
                </button>
            </div>
            {this.state.show && <div
                className="modal"
                tabIndex={-1}
                role="dialog"
                style={{display: 'block'}}
            >
                <div className="modal-dialog">
                    <div className="modal-content">
                        <div className="modal-header">
                            <h5 className="modal-title">{translator.getText('Options')}</h5>
                            <button type="button" className="btn-close" onClick={() => this.setState({show: false})}/>
                        </div>
                        <div className="modal-body">
                            {isOrg &&
                                <div className="form-group">
                                    <p>
                                        {translator.getText('Not public results')}
                                    </p>
                                    <p className="form-text text-danger">
                                        {translator.getText('This function don\'t turn on if results are public!')}
                                    </p>
                                    <button
                                        className={hardVisible ? 'btn btn-outline-warning' : 'btn btn-outline-warning'}
                                        onClick={(event) => {
                                            event.preventDefault();
                                            onSetParams({hardVisible: !hardVisible});
                                        }}>
                                        {hardVisible ? translator.getText('Turn off') : translator.getText('Turn on')}
                                    </button>

                                </div>}
                            <hr/>
                            <div className="form-group">
                                <div className="form-group">
                                    <label>Delay</label>
                                    <input
                                        name="delay"
                                        className="form-control"
                                        value={delay}
                                        type="number"
                                        max={60 * 1000}
                                        min={1000}
                                        step={1000}
                                        onChange={(e) => {
                                            onSetParams({delay: +e.target.value});
                                        }}/>
                                </div>
                            </div>
                            <hr/>
                            <div className="form-group">
                                <label>{translator.getText('Cols')}</label>
                                <input name="cols"
                                       className="form-control"
                                       value={cols}
                                       type="number"
                                       max="3"
                                       min="1"
                                       step={0}
                                       onChange={(e) => {
                                           onSetParams({cols: +e.target.value});
                                       }}
                                />
                            </div>
                            <hr/>
                            <div className="form-group">
                                <label>{translator.getText('Rows')}</label>
                                <input name="rows"
                                       className="form-control"
                                       value={rows}
                                       type="number"
                                       max={100}
                                       min={1}
                                       step={1}
                                       onChange={(e) => {
                                           onSetParams({rows: +e.target.value});
                                       }}/>
                            </div>
                        </div>
                    </div>
                </div>
            </div>}
        </>
    }
}

const mapStateToPros = (state: Store): StateProps => {
    return {
        isOrg: state.presentation.isOrg,
        cols: state.presentation.cols,
        delay: state.presentation.delay,
        rows: state.presentation.rows,
        hardVisible: state.presentation.hardVisible,
    };
};
const mapDispatchToProps = (dispatch: Dispatch<Action<string>>): DispatchProps => {
    return {
        onSetParams: (data) => dispatch({
            data,
            type: ACTION_SET_PARAMS,
        }),
    };
};

export default connect(mapStateToPros, mapDispatchToProps)(Setting);
