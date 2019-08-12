import * as React from 'react';
import { connect } from 'react-redux';
import {
    Action,
    Dispatch,
} from 'redux';
import { setInitialData } from '../actions/';
import {
    InputConnectorItems,
    Store,
} from '../reducers';
import CoreConnector, {
    OwnProps,
    DispatchProps,
    StateProps,
} from './coreConnector';

export class InputConnector extends React.Component<OwnProps, {}> {
    public render() {
        const ConnectedComponent = connect(this.mapStateToProps, this.mapDispatchToProps)(CoreConnector);
        return <ConnectedComponent input={this.props.input}/>;
    }

    private mapDispatchToProps(dispatch: Dispatch<Action<string>>): DispatchProps {
        return {
            onSetInitialData: (data: InputConnectorItems) => dispatch(setInitialData(data)),
        };
    }

    private mapStateToProps(state: Store): StateProps {
        return {
            data: state.inputConnector.data,
        };
    }
}

export default InputConnector;
