import * as React from 'react';
import { connect } from 'react-redux';
import { Dispatch } from 'redux';
import { setInitialData } from '../actions/';
import {
    IInputConnectorItems,
    IInputConnectorStore,
} from '../reducers';
import CoreConnector, { CoreProps } from './core-connector';

interface IState {
    data?: IInputConnectorItems;

    onSetInitialData?(value: IInputConnectorItems): void;
}

export default class InputConnector extends React.Component<CoreProps, {}> {
    public render() {
        const ConnectedComponent = connect(this.mapStateToProps, this.mapDispatchToProps)(CoreConnector);
        return <ConnectedComponent input={this.props.input}/>;
    }

    private mapDispatchToProps(dispatch: Dispatch): IState {
        return {
            onSetInitialData: (data: IInputConnectorItems) => dispatch(setInitialData(data)),
        };
    }

    private mapStateToProps(state: IInputConnectorStore): IState {
        return {
            data: state.inputConnector.data,
        };
    }
}
