import { translator } from '@translator/translator';
import * as React from 'react';
import { connect } from 'react-redux';
import {
    Action,
    Dispatch,
} from 'redux';
import { setHardVisible } from './actions';
import { State as OptionsState } from './reducer';

interface StateProps {
    hardVisible: boolean;
}

interface DispatchProps {
    onHardDisplayChange(status: boolean): void;
}

class HardVisibleSwitch extends React.Component<StateProps & DispatchProps> {

    public render() {
        const {onHardDisplayChange, hardVisible} = this.props;

        return <div className="form-group">
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
                    onHardDisplayChange(!hardVisible);
                }}>
                {hardVisible ? translator.getText('Turn off') : translator.getText('Turn on')}
            </button>

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
