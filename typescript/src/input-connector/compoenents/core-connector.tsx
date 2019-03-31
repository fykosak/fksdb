import * as React from 'react';
import { InputConnectorItems } from '../reducers';

export interface CoreProps {
    input: HTMLInputElement;
}

interface State {
    onSetInitialData?: (value: InputConnectorItems) => void;
    data?: InputConnectorItems;
}

export default class CoreConnector extends React.Component<CoreProps & State, {}> {

    public componentDidMount() {
        const {input, onSetInitialData} = this.props;
        if (input.value) {
            onSetInitialData(JSON.parse(input.value));
        }
    }

    public componentWillReceiveProps(newProps: CoreProps & State) {
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
