import { lang } from '@i18n/i18n';
import * as React from 'react';
import { connect } from 'react-redux';
import {
    Action,
    Dispatch,
} from 'redux';
import { setHardVisible } from '../actions/';
import { State as OptionsState } from '../reducers';

interface StateProps {
    hardVisible: boolean;
}

interface DispatchProps {
    onHardDisplayChange(status: boolean): void;
}

class HardVisibleSwitch extends React.Component<StateProps & DispatchProps, {}> {

    public render() {
        const {onHardDisplayChange, hardVisible} = this.props;

        return <div className="form-group">
            <label>{lang.getText('Not public results')}</label>
            <button
                className={hardVisible ? 'btn btn-outline-warning' : 'btn btn-warning'}
                onClick={(event) => {
                    event.preventDefault();
                    onHardDisplayChange(!hardVisible);
                }}>
                {hardVisible ? lang.getText('Turn off') : lang.getText('Turn on')}
            </button>
            <span
                className="form-text text-danger">{lang.getText('This function don\'t turn on id results are projected!')}</span>
        </div>;
    }
}

const mapStateToProps = (state: { options: OptionsState }): StateProps => {
    return {
        hardVisible: state.options.hardVisible,
    };
};

const mapDispatchToProps = (dispatch: Dispatch<Action<string>>): DispatchProps => {
    return {
        onHardDisplayChange: (status: boolean) => dispatch(setHardVisible(status)),
    };
};

export default connect(
    mapStateToProps,
    mapDispatchToProps,
)(HardVisibleSwitch);
