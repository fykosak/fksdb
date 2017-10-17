import * as React from 'react';
import { connect } from 'react-redux';
import {
    setControlCode,
    setControlInput,
} from '../actions/index';
import { IStore } from '../reducers/index';

interface IState {
    setInput?: (input: HTMLInputElement) => void;
}

class ControlInput extends React.Component<IState & any, {}> {

    public render() {
        const { meta: { valid, error }, setInput, input } = this.props;
        return (
            <span className={'form-group col-lg-2 ' + (valid ? 'has-success' : 'has-error')}>
                <input
                    {...input}
                    maxLength={1}
                    ref={setInput}
                    className={'input-lg control ' + (valid ? 'valid' : 'invalid')}
                    placeholder="X"
                />
                <span className="help-block">{error ? error.msg : 'OK'}</span>
            </span>
        );
    }
}

const mapDispatchToProps = (dispatch): IState => {
    return {
        setInput: (input: HTMLInputElement) => dispatch(setControlInput(input)),
    };
};
export default connect(null, mapDispatchToProps)(ControlInput);
