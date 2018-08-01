import * as React from 'react';
import { connect } from 'react-redux';
import { IStore } from '../../../reducers/';
import { Dispatch } from 'redux';
import { changeEmailValue } from '../../../actions';

// import Lang from '../../../lang/components/lang';
interface IState {
    handleChange?: (value: string) => void;
    valid?: boolean;
}

class Input extends React.Component<IState, {}> {
    public render() {
        const {handleChange, valid} = this.props;
        return <input
            type="email"
            onChange={(event) => {
                const value = event.target.value;
                handleChange(value);
            }
            }
            required={true}
            className={'form-control' + ((!valid) ? ' is-invalid' : '')}
            placeholder="you-mail@example.com"
        />;
    }
}

const mapDispatchToProps = (dispatch: Dispatch<IStore>): IState => {
    return {
        handleChange: (value) => dispatch(changeEmailValue(value)),
    };
};

const mapStateToProps = (state: IStore): IState => {
    if (state.form.hasOwnProperty('email')) {
        return {
            valid: state.form.email.valid,
        };
    }
    return {};
};

export default connect(mapStateToProps, mapDispatchToProps)(Input);
// <Lang text={'email'}/>
