import * as React from 'react';
import { connect } from 'react-redux';
import { Dispatch } from 'redux';
import Lang from '../../lang/components/lang';
import {
    submitFail,
    submitStart,
    submitSuccess,
} from '../../submit/actions/submit';
import {
    netteFetch,
} from '../../submit/middleware/fetch';
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

interface IState {
    submitting?: boolean;
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
    touched: boolean;
}

class Input extends React.Component<IProps & IState, ICustomState> {
    public constructor() {
        super();
        this.state = {
            error: undefined,
            touched: false,
            value: "",
        };
    }

    public componentDidMount() {
        this.handleOnChange("");
    }

    public render() {
        const {onSubmitSuccess, onSubmitFail, onSubmitStart, submitting} = this.props;
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
        const {touched} = this.state;
        return <>
            <div className={'form-group was-validated'}>
                <label><Lang text={'E-mail'}/></label>
                <input onChange={(e) => {
                    this.handleOnChange(e.target.value);
                }}
                       onFocus={() => {
                           this.setState({touched: true});
                       }}
                       type="email"
                       required={true}
                       className={'form-control' + ((touched && valid) ? ' is-invalid' : '')}
                       placeholder="yourmail@example.com"
                />
                {this.state.error && <span className="invalid-feedback">{this.state.error}</span>}

            </div>
            <button className="btn btn-primary" disabled={!valid || submitting} onClick={onSearchButtonClick}>
                <Lang text={'hledat'}/>
            </button>
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
        onSubmitFail: (e) => dispatch(submitFail(e, 'personProvider')),
        onSubmitStart: () => dispatch(submitStart('personProvider')),
        onSubmitSuccess: (data: IReceiveProviderData<IReceiveProviderFields>) =>
            dispatch(submitSuccess<IReceiveProviderData<IReceiveProviderFields>>(data, 'personProvider')),
    };
};

const mapStateToProps = (state: IStore): IState => {
    if (state.submit.hasOwnProperty('personProvider')) {
        return {
            submitting: state.submit.personProvider.submitting,
        };
    }
    return {};

};

export default connect(mapStateToProps, mapDispatchToProps)(Input);
