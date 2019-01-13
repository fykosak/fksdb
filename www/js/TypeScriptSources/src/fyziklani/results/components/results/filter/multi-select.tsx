import * as React from 'react';
import { connect } from 'react-redux';
import {
    Action,
    Dispatch,
} from 'redux';
import { lang } from '../../../../../i18n/i18n';
import HardVisibleSwitch from '../../../../helpers/options/compoents/hard-visible-switch';
import { setCols } from '../../../actions/Presentation/SetCols';
import { setDelay } from '../../../actions/Presentation/SetDelay';
import { setRows } from '../../../actions/Presentation/SetRows';
import { IFyziklaniResultsStore } from '../../../reducers';
import AutoSwitchControl from './auto-switch-control';

interface IState {
    delay?: number;
    cols?: number;
    rows?: number;
    isOrg?: boolean;

    onSetDelay?(position: number): void;

    onSetCols?(cols: number): void;

    onSetRows?(rows: number): void;
}

class Select extends React.Component<IState, {}> {

    public render() {
        const {isOrg, delay, cols, rows, onSetDelay, onSetCols, onSetRows} = this.props;
//  <button type="button" className="btn btn-primary" data-toggle="modal" data-target="#fyziklaniResultsOptionModal">
//                 <i className="fa fa-gear"/>
//        {autoSwitch ? (<MultiFilterControl/>) : (null)}
//             </button>
        return <div className="form-group">

            <div className="modal fade" id="fyziklaniResultsOptionModal" tabIndex={-1} role="dialog">
                <div className="modal-dialog" role="document">
                    <div className="modal-content">
                        <div className="modal-header">
                            <h5 className="modal-title">{lang.getText('Options')}</h5>
                            <button type="button" className="close" data-dismiss="modal">
                                <span>&times;</span>
                            </button>
                        </div>
                        <div className="modal-body">
                            {isOrg && <HardVisibleSwitch/>}
                            <hr/>
                            <AutoSwitchControl/>
                            <hr/>
                            <div className={'form-group'}>
                                <label>Delay</label>
                                <input name={'delay'} className={'form-control'} value={delay} type={'number'} max={60 * 1000} min={1000}
                                       step={1000}
                                       onChange={(e) => {
                                           onSetDelay(+e.target.value);
                                       }}/>
                            </div>
                            <hr/>
                            <div className={'form-group'}>
                                <label>Cols</label>
                                <input name={'cols'} className={'form-control'} value={cols} type={'number'} max={3} min={1}
                                       step={0}
                                       onChange={(e) => {
                                           onSetCols(+e.target.value);
                                       }}/>
                            </div>
                            <hr/>
                            <div className={'form-group'}>
                                <label>Rows</label>
                                <input name={'rows'} className={'form-control'} value={rows} type={'number'} max={100} min={1}
                                       step={1}
                                       onChange={(e) => {
                                           onSetRows(+e.target.value);
                                       }}/>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
            ;
    }
}

const mapStateToPros = (state: IFyziklaniResultsStore): IState => {
    return {
        cols: state.presentation.cols,
        delay: state.presentation.delay,
        isOrg: state.options.isOrg,
        rows: state.presentation.rows,
    };
};
const mapDispatchToProps = (dispatch: Dispatch<Action<string>>): IState => {
    return {
        onSetCols: (cols: number) => dispatch(setCols(cols)),
        onSetDelay: (position: number) => dispatch(setDelay(position)),
        onSetRows: (rows: number) => dispatch(setRows(rows)),
    };
};

export default connect(mapStateToPros, mapDispatchToProps)(Select);
