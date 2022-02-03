import { translator } from '@translator/translator';
import * as React from 'react';
import { connect } from 'react-redux';
import HardVisibleSwitch from '../../Helpers/HardVisible/Component';
import { FyziklaniResultsPresentationStore } from '../Reducers';
import { Action, Dispatch } from 'redux';
import {
    Params,
    setParams,
} from 'FKSDB/Components/Controls/Fyziklani/ResultsAndStatistics/ResultsPresentation/actions';

interface StateProps {
    isOrg: boolean;
    delay: number;
    rows: number;
    cols: number;
}

interface DispatchProps {
    onSetParams(data: Params): void;
}

class Setting extends React.Component<StateProps & DispatchProps> {

    public render() {
        const {isOrg, onSetParams, cols, delay, rows} = this.props;
        return <div className="modal fade" id="fyziklaniResultsOptionModal" tabIndex={-1} role="dialog">
            <div className="modal-dialog" role="document">
                <div className="modal-content">
                    <div className="modal-header">
                        <h5 className="modal-title">{translator.getText('Options')}</h5>
                        <button type="button" className="close" data-dismiss="modal">
                            <span>&times;</span>
                        </button>
                    </div>
                    <div className="modal-body">
                        {isOrg && <HardVisibleSwitch/>}
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
        </div>;
    }
}

const mapStateToPros = (state: FyziklaniResultsPresentationStore): StateProps => {
    return {
        isOrg: state.options.isOrg,
        cols: state.presentation.cols,
        delay: state.presentation.delay,
        rows: state.presentation.rows,
    };
};
const mapDispatchToProps = (dispatch: Dispatch<Action<string>>): DispatchProps => {
    return {
        onSetParams: (data) => dispatch(setParams(data)),
    };
};

export default connect(mapStateToPros, mapDispatchToProps)(Setting);
