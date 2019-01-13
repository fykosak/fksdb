import * as React from 'react';
import { connect } from 'react-redux';
import {
    Action,
    Dispatch,
} from 'redux';
import { lang } from '../../../../../../i18n/i18n';
import { setCols } from '../../../../actions/presentation/setCols';
import { FyziklaniResultsStore } from '../../../../reducers';

interface State {
    delay?: number;
    cols?: number;
    rows?: number;
    isOrg?: boolean;

    onSetDelay?(position: number): void;

    onSetCols?(cols: number): void;

    onSetRows?(rows: number): void;
}

class ColsField extends React.Component<State, {}> {

    public render() {
        const {cols, onSetCols} = this.props;
        return <div className={'form-group'}>
            <label>{lang.getText('Cols')}</label>
            <input name={'cols'} className={'form-control'} value={cols} type={'number'} max={3} min={1}
                   step={0}
                   onChange={(e) => {
                       onSetCols(+e.target.value);
                   }}/>
        </div>;
    }
}

const mapStateToPros = (state: FyziklaniResultsStore): State => {
    return {
        cols: state.presentation.cols,

    };
};
const mapDispatchToProps = (dispatch: Dispatch<Action<string>>): State => {
    return {
        onSetCols: (cols: number) => dispatch(setCols(cols)),

    };
};

export default connect(mapStateToPros, mapDispatchToProps)(ColsField);
