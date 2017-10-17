import * as React from 'react';
import {
    connect,
    Dispatch,
} from 'react-redux';
import {

    setTaskInput,
} from '../../actions/index';

import { IStore } from '../../reducers/index';

interface IState {
    setInput?: (input: HTMLInputElement) => void;
}

class TaskInput extends React.Component<IState & any, {}> {

    public render() {
        const { meta: { valid, error }} = this.props;
        return (
            <span className={'form-group col-5 ' + (valid ? 'has-success' : 'has-error')}>
                <span className="help-block">{error ? error.msg : 'OK'}</span>
            </span>

        );
    }
}

const mapDispatchToProps = (dispatch: Dispatch<IStore>): IState => {
    return {
        setInput: (input: HTMLInputElement) => dispatch(setTaskInput(input)),
    };
};
export default connect(null, mapDispatchToProps)(TaskInput);
