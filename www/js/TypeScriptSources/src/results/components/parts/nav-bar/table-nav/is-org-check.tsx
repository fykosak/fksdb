import * as React from 'react';
import {
    connect,
} from 'react-redux';
import { IStore } from '../../../../reducers';

import {
    setHardVisible,
} from '../../../../actions/options';

interface IState {
    onHardDisplayChange?: (status: boolean) => void;
    isOrg?: boolean;
    hardVisible?: boolean;
}

class IsOrgCheck extends React.Component<IState, {}> {

    public render() {
        const {isOrg, onHardDisplayChange, hardVisible} = this.props;

        return (
            <div className="form-group has-error">
                <div className="checkbox">
                    <label>
                        <input
                            type="checkbox"
                            disabled={!isOrg}
                            value="1"
                            checked={hardVisible}
                            onChange={(event) => onHardDisplayChange(event.target.checked)}/>Neveřejné výsledkovky, <span
                        className="text-danger">tuto funkci nezapínejte pokud jsou výsledkovky promítané!!!</span>
                    </label>
                </div>
            </div>
        );
    }
}

const mapStateToProps = (state: IStore): IState => {
    return {
        hardVisible: state.options.hardVisible,
        isOrg: state.options.isOrg,
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
)(IsOrgCheck);
