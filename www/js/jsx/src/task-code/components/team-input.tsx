import * as React from 'react';
import {
    connect,
    Dispatch,
} from 'react-redux';
import {
    setTeamCode,
    setTeamInput,
} from '../actions/index';
import { IStore } from '../reducers/index';
import { WrappedFieldProps } from 'redux-form';

interface ITeam {
    team_id: number;
    name: string;
}
interface IProps {
    teams: ITeam[];
}

interface IState {
    setInput?: (input: HTMLInputElement) => void;
}

class TeamInput extends React.Component<IProps & IState & any, {}> {

    public render() {
        const { meta: { valid, error }, setInput, input } = this.props;

        return (
            <span className={'form-group col-lg-5 ' + (valid ? 'has-success' : 'has-error')}>
                <input
                    {...input}
                    maxLength={6}
                    ref={setInput}
                    className={'input-lg team ' + (valid === false ? 'invalid' : (valid === true ? 'valid' : ''))}
                    placeholder="XXXXXX"
                />
                <span className="help-block">{error ? error.msg : 'OK'}</span>
            </span>

        );
    }

}

const mapDispatchToProps = (dispatch: Dispatch<IStore>): IState => {
    return {
        setInput: (input: HTMLInputElement) => dispatch(setTeamInput(input)),
    };
};
export default connect(null, mapDispatchToProps)(TeamInput);
