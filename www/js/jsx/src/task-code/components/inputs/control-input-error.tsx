import * as React from 'react';
import { connect } from 'react-redux';
import {
    setControlInput,
} from '../../actions/index';

interface IState {
    setInput?: (input: HTMLInputElement) => void;
}

class ControlInput extends React.Component<IState & any, {}> {

    public render() {
        const { meta: { valid, error } } = this.props;
        return (
            <span className={'form-group col-2 ' + (valid ? 'has-success' : 'has-error')}>
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
