import { translator } from '@translator/translator';
import * as React from 'react';
import { connect } from 'react-redux';
import {
    Action,
    Dispatch,
} from 'redux';
import { setRows } from '../../actions';
import { FyziklaniResultsPresentationStore } from '../../Reducers';

interface StateProps {
    rows: number;
}

interface DispatchProps {
    onSetRows(rows: number): void;
}

class RowsField extends React.Component<StateProps & DispatchProps, {}> {

    public render() {
        const {rows, onSetRows} = this.props;
        return <div className={'form-group'}>
            <label>{translator.getText('Rows')}</label>
            <input name={'rows'} className={'form-control'} value={rows} type={'number'} max={100} min={1}
                   step={1}
                   onChange={(e) => {
                       onSetRows(+e.target.value);
                   }}/>
        </div>;
    }
}

const mapStateToPros = (state: FyziklaniResultsPresentationStore): StateProps => {
    return {
        rows: state.presentation.rows,
    };
};
const mapDispatchToProps = (dispatch: Dispatch<Action<string>>): DispatchProps => {
    return {
        onSetRows: (rows: number) => dispatch(setRows(rows)),
    };
};

export default connect(mapStateToPros, mapDispatchToProps)(RowsField);
