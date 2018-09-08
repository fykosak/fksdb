import * as React from 'react';
import { connect } from 'react-redux';
import { Dispatch } from 'redux';
import { setInitialData } from '../actions';
import { IAccommodationStore } from '../reducer';

interface IProps {
    input: HTMLInputElement;
}

interface IValues {
    [key: string]: number;
}

interface IState {
    onSetInitialData?: (value: any) => void;
    data?: IValues;
}

class InputConnector extends React.Component<IProps & IState, {}> {

    public componentDidMount() {
        const {input, onSetInitialData} = this.props;
        if (input.value) {
            onSetInitialData(JSON.parse(input.value));
        }
    }

    public componentWillReceiveProps(newProps: IProps & IState) {
        const data: IValues = {};
        let hasValue = false;

        for (const key in newProps.data) {
            if (newProps.data.hasOwnProperty(key) && (newProps.data[key] !== null)) {
                data[key] = newProps.data[key];
                hasValue = true;
            }
        }
        this.props.input.value = hasValue ? JSON.stringify(data) : null;
    }

    public render() {
        return null;
    }
}

const mapDispatchToProps = (dispatch: Dispatch<IAccommodationStore>): IState => {
    return {
        onSetInitialData: (data) => dispatch(setInitialData(data)),
    };
};

const mapStateToProps = (state): IState => {

    return {
        data: state.accommodation,
    };
};

export default connect(mapStateToProps, mapDispatchToProps)(InputConnector);
