import * as React from 'react';
import { connect } from 'react-redux';
import { Dispatch } from 'redux';
import { setInitialData } from '../actions';

interface IProps {
    input: HTMLInputElement;
}

interface IState {
    onSetInitialData?: (value: any) => void;
    data?: any;
}

class InputConnector extends React.Component<IProps & IState, {}> {

    public componentDidMount() {
        const {input, onSetInitialData} = this.props;
        if (input.value) {
            onSetInitialData(JSON.parse(input.value));
        }
    }

    public componentWillReceiveProps(newProps) {
        this.props.input.value = JSON.stringify(newProps.data);
    }

    public render() {
        return null;
    }
}

const mapDispatchToProps = (dispatch: Dispatch<any>, ownProps: IProps): IState => {
    return {
        onSetInitialData: (data) => dispatch(setInitialData(data)),
    };
};

const mapStateToProps = (state, ownProps: IProps): IState => {

    return {
        data: state.accommodation,
    };
};

export default connect(mapStateToProps, mapDispatchToProps)(InputConnector);
