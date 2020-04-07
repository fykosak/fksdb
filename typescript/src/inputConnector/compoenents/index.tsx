import { setInitialData } from '@inputConnector/actions';
import * as React from 'react';
import { connect } from 'react-redux';
import {
    Action,
    Dispatch,
} from 'redux';
import {
    InputConnectorItems,
    Store,
} from '../reducers';

export interface OwnProps {
    input: HTMLInputElement;
}

export interface StateProps {
    data: InputConnectorItems;
}

export interface DispatchProps {
    onSetInitialData(value: InputConnectorItems): void;
}

class NetteInputConnector extends React.Component<OwnProps & StateProps & DispatchProps, {}> {

    public componentDidMount() {
        const {input, onSetInitialData} = this.props;
        if (input.value) {
            onSetInitialData(JSON.parse(input.value));
        }
    }

    public componentWillReceiveProps(newProps: OwnProps & StateProps & DispatchProps) {
        const data: InputConnectorItems = {};
        let hasValue = false;

        for (const key in newProps.data) {
            if (newProps.data.hasOwnProperty(key) && (newProps.data[key] !== null)) {
                data[key] = newProps.data[key];
                hasValue = true;
            }
        }
        this.props.input.value = hasValue ? JSON.stringify(data) : null;
        this.props.input.dispatchEvent(new Event('change')); // netteForm compatibility
    }

    public render() {
        return null;
    }
}

const mapDispatchToProps = (dispatch: Dispatch<Action<string>>): DispatchProps => {
    return {
        onSetInitialData: (data: InputConnectorItems) => dispatch(setInitialData(data)),
    };
};

const mapStateToProps = (state: Store): StateProps => {
    return {
        data: state.inputConnector.data,
    };
};
export default connect(mapStateToProps, mapDispatchToProps)(NetteInputConnector);
