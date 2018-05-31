import * as React from 'react';
import { connect } from 'react-redux';
import { Dispatch } from 'redux';
import {
    submitFail,
    submitStart,
    submitSuccess,
} from '../../fetch-api/actions/submit';
import {
    netteFetch,
} from '../../fetch-api/middleware/fetch';
import { IResponse } from '../../fetch-api/middleware/interfaces';
import Lang from '../../lang/components/lang';
import {
    IRequestData,
    IResponseData,
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
    onSubmitSuccess?: (data: IResponse<IResponseData>) => void;
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
            netteFetch<IRequestData, IResponseData>({
                act: 'person-provider',
                data: {
                    email: this.state.value,
                    fields: [],
                },
            }, (response) => {
                response.data.key = this.props.accessKey;
                onSubmitSuccess(response);
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
        onSubmitSuccess: (data) =>
            dispatch(submitSuccess<IResponseData>(data, 'personProvider')),
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
