import * as React from 'react';
import { connect } from 'react-redux';
import {
    Action,
    Dispatch,
} from 'redux';
import { lang } from '../../../../i18n/i18n';
import { setHardVisible } from '../actions/';
import { State as OptionsState } from '../reducers';

interface State {
    hardVisible?: boolean;

    onHardDisplayChange?(status: boolean): void;
}

class HardVisibleSwitch extends React.Component<State, {}> {

    public render() {
        const {onHardDisplayChange, hardVisible} = this.props;

        return <div className="form-group">
            <label>{lang.getText('Not public results')}</label>
            <button
                className={hardVisible ? 'btn btn-outline-danger' : 'btn btn-danger'}
                onClick={(event) => {
                    event.preventDefault();
                    onHardDisplayChange(!hardVisible);
                }}>
                {hardVisible ? lang.getText('Turn off') : lang.getText('Turn on')}
            </button>
            <span className="form-text text-danger">{lang.getText('This function don\'t turn on id results are projected!')}</span>
        </div>;
    }
}

const mapStateToProps = (state: { options: OptionsState }): State => {
    return {
        hardVisible: state.options.hardVisible,
    };
};

const mapDispatchToProps = (dispatch: Dispatch<Action<string>>): State => {
    return {
        onHardDisplayChange: (status: boolean) => dispatch(setHardVisible(status)),
    };
};

export default connect(
    mapStateToProps,
    mapDispatchToProps,
)(HardVisibleSwitch);
