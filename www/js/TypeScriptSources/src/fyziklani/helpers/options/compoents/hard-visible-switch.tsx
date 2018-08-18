import * as React from 'react';
import { connect } from 'react-redux';
import { setHardVisible } from '../actions/';
import { IFyziklaniOptionsState } from '../reducers';

interface IState {
    onHardDisplayChange?: (status: boolean) => void;
    hardVisible?: boolean;
}

class HardVisibleSwitch extends React.Component<IState, {}> {

    public render() {
        const {onHardDisplayChange, hardVisible} = this.props;

        return <div className="form-group">
            <button
                className={'btn btn-waring'}
                onClick={(event) => {
                    event.preventDefault();
                    onHardDisplayChange(!hardVisible)
                }}>
                {hardVisible ? 'vyponout' : 'zapnout'}"Neveřejné výsledkovky"
            </button>
            <span className="text-danger">Tuto funkci nezapínejte pokud jsou výsledkovky promítané!!!</span>
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
