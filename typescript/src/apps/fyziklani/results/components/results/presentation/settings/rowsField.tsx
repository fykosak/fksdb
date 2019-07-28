import { lang } from '@i18n/i18n';
import * as React from 'react';
import { connect } from 'react-redux';
import {
    Action,
    Dispatch,
} from 'redux';
import { setRows } from '../../../../actions/presentation/setRows';
import { FyziklaniResultsStore } from '../../../../reducers';

interface State {
    rows?: number;

    onSetRows?(rows: number): void;
}

class RowsField extends React.Component<State, {}> {

    public render() {
        const {rows, onSetRows} = this.props;
        return <div className={'form-group'}>
            <label>{lang.getText('Rows')}</label>
            <input name={'rows'} className={'form-control'} value={rows} type={'number'} max={100} min={1}
                   step={1}
                   onChange={(e) => {
                       onSetRows(+e.target.value);
                   }}/>
        </div>;
    }
}

const mapStateToPros = (state: FyziklaniResultsStore): State => {
    return {
        rows: state.presentation.rows,
    };
};
const mapDispatchToProps = (dispatch: Dispatch<Action<string>>): State => {
    return {
        onSetRows: (rows: number) => dispatch(setRows(rows)),
    };
};

export default connect(mapStateToPros, mapDispatchToProps)(RowsField);
