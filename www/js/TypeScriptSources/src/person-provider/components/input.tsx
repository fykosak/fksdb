import * as React from 'react';
import { netteFetch } from '../../shared/helpers/fetch';
import { IReceiveData } from '../../shared/interfaces';
import {
    IReceiveProviderData,
    IReceiveProviderFields,
    IResponseValues,
    IStore,
} from '../interfaces';
import {
    isMail,
    required,
} from '../validation';
import { Dispatch } from 'redux';
import {
    submitFail,
    submitStart,
    submitSuccess,
} from '../../shared/actions/submit';
import { connect } from 'react-redux';

interface IState {
    onSubmitFail?: (e) => void;
    onSubmitStart?: () => void;
    onSubmitSuccess?: (data: IReceiveProviderData<IReceiveProviderFields>) => void;
}

interface IProps {
    accessKey: string;
}

interface ICustomState {
    value: string;
    error?: string;
}

class Input extends React.Component<IProps & IState, ICustomState> {
    public constructor() {
        super();
        this.state = {
            error: undefined,
            value: "",
        };
    }

    public componentDidMount() {
        this.handleOnChange("");
    }

    public render() {
        const {onSubmitSuccess, onSubmitFail, onSubmitStart} = this.props;
        const onSearchButtonClick = (event) => {
            event.preventDefault();
            onSubmitStart();
            netteFetch<IResponseValues, IReceiveProviderData<IReceiveProviderFields>>({
                act: 'person-provider',
                email: this.state.value,
                fields: [],
            }, (data) => {
                onSubmitSuccess({...data, key: this.props.accessKey});
            }, onSubmitFail);
        };
        const valid = !this.state.error;
        return <>
            <div className={'form-group was-validated'}>
                <label>E-mail</label>
                <input onChange={(e) => {
                    this.handleOnChange(e.target.value);
                }}
                       type="email"
                       required={true}
                       className={'form-control' + (valid ? ' is-invalid' : '')}
                       placeholder="yourmail@example.com"
                />
                {this.state.error && <span className="invalid-feedback">{this.state.error}</span>}

            </div>
            <button className="btn btn-primary" disabled={!valid} onClick={onSearchButtonClick}>Search</button>
        </>;
    }

    private handleOnChange(value: string): void {
        const tests = [isMail, required];
        const error = tests.reduce((log: string, test: (value: string) => string): string => {
            return test(value) || log;
        }, undefined);
        this.setState({error, value});
    }

}

const mapDispatchToProps = (dispatch: Dispatch<IStore>): IState => {
    return {
        onSubmitFail: (e) => dispatch(submitFail(e)),
        onSubmitStart: () => dispatch(submitStart()),
        onSubmitSuccess: (data: IReceiveProviderData<IReceiveProviderFields>) =>
            dispatch(submitSuccess<IReceiveProviderData<IReceiveProviderFields>>(data)),
    };
};

const mapStateToProps = (): IState => {
    return {};
};

export default connect(mapStateToProps, mapDispatchToProps)(Input);
