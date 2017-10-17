import * as React from 'react';
import {
    connect,
    Dispatch,
} from 'react-redux';
import {
    setTaskCode,
    setTaskInput,
} from '../actions/index';
import { ITask } from '../middleware/interfaces';
import { IStore } from '../reducers/index';


interface IState {
    setInput?: (input: HTMLInputElement) => void;
}

class TaskInput extends React.Component<IState & any, {}> {

    public render() {
        const { meta: { valid, error }, setInput, input } = this.props;
        return (
            <span className={'form-group col-lg-5 ' + (valid ? 'has-success' : 'has-error')}>
                <input
                    {...input}
                    maxLength={2}
                    ref={setInput}
                    className={'input-lg task ' + (valid === false ? 'invalid' : (valid === true ? 'valid' : ''))}
                    placeholder="XX"
                />
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
