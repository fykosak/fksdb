import { lang } from '@i18n/i18n';
import * as React from 'react';
import { connect } from 'react-redux';
import {
    Action,
    Dispatch,
} from 'redux';
import { setCols } from '../../actions/setCols';
import { FyziklaniResultsPresentationStore } from '../../reducers';

interface StateProps {
    cols: number;
}

interface DispatchProps {
    onSetCols(cols: number): void;
}

class ColsField extends React.Component<StateProps & DispatchProps, {}> {

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

const mapStateToPros = (state: FyziklaniResultsPresentationStore): StateProps => {
    return {
        cols: state.presentation.cols,
    };
};
const mapDispatchToProps = (dispatch: Dispatch<Action<string>>): DispatchProps => {
    return {
        onSetCols: (cols: number) => dispatch(setCols(cols)),

    };
};

export default connect(mapStateToPros, mapDispatchToProps)(ColsField);
