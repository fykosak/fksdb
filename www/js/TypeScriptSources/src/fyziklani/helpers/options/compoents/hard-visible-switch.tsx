import * as React from 'react';
import { connect } from 'react-redux';
import { lang } from '../../../../i18n/i18n';
import { setHardVisible } from '../actions/';
import { IFyziklaniOptionsState } from '../reducers';

interface IState {
    hardVisible?: boolean;

    onHardDisplayChange?(status: boolean): void;
}

class HardVisibleSwitch extends React.Component<IState, {}> {

    public render() {
        const {onHardDisplayChange, hardVisible} = this.props;

        return <div className="form-group">
            <button
                className={'btn btn-waring'}
                onClick={(event) => {
                    event.preventDefault();
                    onHardDisplayChange(!hardVisible);
                }}>
                {hardVisible ? lang.getText('turn off') : lang.getText('turn on')} {lang.getText('"Not public results"')}
            </button>
            <span className="text-danger">{lang.getText('This function don\'t turn on id results are projected!')}</span>
        </div>;
    }
}

const mapStateToProps = (state: { options: IFyziklaniOptionsState }): IState => {
    return {
        hardVisible: state.options.hardVisible,
    };
};

const mapDispatchToProps = (dispatch): IState => {
    return {
        onHardDisplayChange: (status: boolean) => dispatch(setHardVisible(status)),
    };
};

export default connect(
    mapStateToProps,
    mapDispatchToProps,
)(HardVisibleSwitch);
