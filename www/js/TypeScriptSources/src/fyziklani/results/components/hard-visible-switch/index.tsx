import * as React from 'react';
import { connect } from 'react-redux';

import { IFyziklaniResultsStore } from '../../reducers';
import { setHardVisible } from '../../../helpers/options/actions';

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

const mapStateToProps = (state: IFyziklaniResultsStore): IState => {
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
