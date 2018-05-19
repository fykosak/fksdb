import * as React from 'react';
import {
    Field,
} from 'redux-form';
import Input from './input';

import { connect } from 'react-redux';
import { Dispatch } from 'redux';

import {
    submitFail,
    submitStart,
    submitSuccess,
} from '../../shared/actions/submit';
import { IReceiveData } from '../../shared/interfaces';
import {
    IReceiveProviderData,
    IStore,
} from '../interfaces';
import {
    isMail,
    required,
} from '../validation';

interface IProps {
    accessKey: string;
}

interface IState {
    onSubmitFail?: (e) => void;
    onSubmitStart?: () => void;
    onSubmitSuccess?: (data: IReceiveData<IReceiveProviderData>) => void;
    personId?: { hasValue: boolean; value: string };
}

class PersonProvider extends React.Component<IProps & IState, {}> {

    public render() {
        if (this.props.personId) {
            return <div>
                {this.props.children}
            </div>;
        } else {
            return <>
                <Field name={'email'}
                       component={Input}
                       validate={[required, isMail]}
                       onSubmitFail={(e) => {
                           this.props.onSubmitFail(e);
                       }}
                       onSubmitStart={() => {
                           this.props.onSubmitStart();
                       }}
                       onSubmitSuccess={(data) => {
                           data.key = this.props.accessKey;
                           this.props.onSubmitSuccess(data);
                       }}
                />
            </>;
        }
    }
}

const mapDispatchToProps = (dispatch: Dispatch<IStore>): IState => {
    return {
        onSubmitFail: (e) => dispatch(submitFail(e)),
        onSubmitStart: () => dispatch(submitStart()),
        onSubmitSuccess: (data: IReceiveData<IReceiveProviderData>) => dispatch(submitSuccess<IReceiveData<IReceiveProviderData>>(data)),
    };
};

const mapStateToProps = (state: IStore, ownProps: IProps): IState => {
    const accessKey = ownProps.accessKey;
    if (state.provider.hasOwnProperty(accessKey)) {
        return {
            personId: state.provider[accessKey].personId,
        };
    }
    return {};
};

export default connect(mapStateToProps, mapDispatchToProps)(PersonProvider);
