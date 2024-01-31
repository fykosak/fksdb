import * as React from 'react';
import { useContext, useState } from 'react';
import { useDispatch, useSelector } from 'react-redux';
import { ACTION_SET_PARAMS } from '../../actions/presentation';
import { Store } from 'FKSDB/Components/Game/ResultsAndStatistics/reducers/store';
import { TranslatorContext } from '@translator/context';

export default function Setting() {

    const [show, setShow] = useState<boolean>(false);
    const translator = useContext(TranslatorContext);
    const isOrganizer = useSelector((state: Store) => state.presentation.isOrganizer);
    const cols = useSelector((state: Store) => state.presentation.cols);
    const delay = useSelector((state: Store) => state.presentation.delay);
    const rows = useSelector((state: Store) => state.presentation.rows);
    const hardVisible = useSelector((state: Store) => state.presentation.hardVisible);
    const dispatch = useDispatch();
    return <>
        <div className="fixed-bottom float-start" style={{zIndex: 10001}}>
            <button
                type="button"
                className="btn btn-link"
                onClick={() => setShow(!show)}
            >
                <i className="fas fa-cogs"/>
            </button>
        </div>
        {show && <div
            className="modal"
            tabIndex={-1}
            role="dialog"
            style={{display: 'block'}}
        >
            <div className="modal-dialog">
                <div className="modal-content">
                    <div className="modal-header">
                        <h5 className="modal-title">{translator.getText('Options')}</h5>
                        <button type="button" className="btn-close" onClick={() => setShow(false)}/>
                    </div>
                    <div className="modal-body">
                        {isOrganizer &&
                            <div className="form-group">
                                <p>
                                    {translator.getText('Not public results')}
                                </p>
                                <p className="form-text text-danger">
                                    {translator.getText('Don\'t turn on this function don\'t turn on if results are public!')}
                                </p>
                                <button
                                    className={hardVisible ? 'btn btn-outline-warning' : 'btn btn-outline-warning'}
                                    onClick={(event) => {
                                        event.preventDefault();
                                        dispatch({data: {hardVisible: !hardVisible}, type: ACTION_SET_PARAMS});
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
                                    onChange={(e) => dispatch({
                                        data: {delay: +e.target.value},
                                        type: ACTION_SET_PARAMS,
                                    })}/>
                            </div>
                        </div>
                        <hr/>
                        <div className="form-group">
                            <label>{translator.getText('Cols')}</label>
                            <input
                                name="cols"
                                className="form-control"
                                value={cols}
                                type="number"
                                max="3"
                                min="1"
                                step={0}
                                onChange={(e) => dispatch({data: {cols: +e.target.value}, type: ACTION_SET_PARAMS})}
                            />
                        </div>
                        <hr/>
                        <div className="form-group">
                            <label>{translator.getText('Rows')}</label>
                            <input
                                name="rows"
                                className="form-control"
                                value={rows}
                                type="number"
                                max={100}
                                min={1}
                                step={1}
                                onChange={(e) => dispatch({data: {rows: +e.target.value}, type: ACTION_SET_PARAMS})}
                            />
                        </div>
                    </div>
                </div>
            </div>
        </div>}
    </>
}
