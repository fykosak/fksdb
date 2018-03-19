import * as React from 'react';
import {
    connect,
} from 'react-redux';
import {
    setAutoSwitch,
} from '../../../../actions/table-filter';

import { IStore } from '../../../../reducers/index';

interface IState {
    onChangeAutoSwitch?: (status: boolean) => void;
    autoSwitch?: boolean;
}

class AutoSwitchCheck extends React.Component<IState, {}> {

    public render() {
        const {onChangeAutoSwitch, autoSwitch} = this.props;
        return (
            <div className="form-group">
                <div className="checkbox">
                    <label>
                        <input
                            type="checkbox"
                            value="1"
                            checked={autoSwitch}
                            onChange={(event) => {
                                onChangeAutoSwitch(event.target.checked);
                            }}/>Automatické scrollovanie a prepínanie. </label>
                </div>
            </div>
        );
    }
}

const mapStateToProps = (state: IStore): IState => {
    return {
        autoSwitch: state.tableFilter.autoSwitch,
    };
};

const mapDispatchToProps = (dispatch): IState => {
    return {
        onChangeAutoSwitch: (status: boolean) => dispatch(setAutoSwitch(status)),
    };
};

export default connect(
    mapStateToProps,
    mapDispatchToProps,
)(AutoSwitchCheck);
