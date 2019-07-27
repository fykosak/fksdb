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
import CoreConnector, { CoreProps } from './CoreConnector';

interface State {
    data?: InputConnectorItems;

    onSetInitialData?(value: InputConnectorItems): void;
}

export class InputConnector extends React.Component<CoreProps, {}> {
    public render() {
        const ConnectedComponent = connect(this.mapStateToProps, this.mapDispatchToProps)(CoreConnector);
        return <ConnectedComponent input={this.props.input}/>;
    }

    private mapDispatchToProps(dispatch: Dispatch<Action<string>>): State {
        return {
            onSetInitialData: (data: InputConnectorItems) => dispatch(setInitialData(data)),
        };
    }

    private mapStateToProps(state: Store): State {
        return {
            data: state.inputConnector.data,
        };
    }
}

export default InputConnector;
