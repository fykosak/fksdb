import * as React from 'react';
import { connect } from 'react-redux';
import {
    setControlInput,
} from '../../actions/index';

interface IState {
    setInput?: (input: HTMLInputElement) => void;
}
interface IProps {
    noRefMode?: boolean;
}

class ControlInput extends React.Component<IState & IProps & any, {}> {

    public render() {
        const { meta: { valid }, setInput, input, noRefMode } = this.props;
        return (
            <span className={'form-group col-2 ' + (valid ? 'has-success' : 'has-error')}>
                <input
                    {...input}
                    maxLength={1}
                    ref={noRefMode ? null : setInput}
                    className={'input-lg control ' + (valid ? 'valid' : 'invalid')}
                    placeholder="X"
                />
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
