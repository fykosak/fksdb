import { setInitialData } from 'vendor/fykosak/nette-frontend-component/src/InputConnector/actions';
import * as React from 'react';
import { connect } from 'react-redux';
import {
    Action,
    Dispatch,
} from 'redux';
import { InputConnectorStateMap } from 'vendor/fykosak/nette-frontend-component/src/InputConnector/reducer';

export interface OwnProps {
    input: HTMLInputElement | HTMLSelectElement;
}

export interface StateProps {
    value: number;
}

export interface DispatchProps {
    onSetInitialData(value: number): void;
}

class InputConnector2 extends React.Component<OwnProps & StateProps & DispatchProps> {

    public componentDidMount() {
        const {input, onSetInitialData} = this.props;
        if (input.value) {
            onSetInitialData(+input.value);
        }
    }

    public UNSAFE_componentWillReceiveProps(newProps: OwnProps & StateProps & DispatchProps) {
        this.props.input.value = newProps.value ? newProps.value.toString() : null;
        this.props.input.dispatchEvent(new Event('change')); // netteForm compatibility
    }

    public render() {
        return null;
    }
}

const mapDispatchToProps = (dispatch: Dispatch<Action<string>>): DispatchProps => {
    return {
        onSetInitialData: (value: number) => dispatch(setInitialData({data: value})),
    };
};

const mapStateToProps = (state: { inputConnector: InputConnectorStateMap }): StateProps => {
    return {
        value: state.inputConnector.data && +state.inputConnector.data.data,
    };
};
export default connect(mapStateToProps, mapDispatchToProps)(InputConnector2);
