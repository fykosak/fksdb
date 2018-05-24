import * as React from 'react';
import {
    Field,
} from 'redux-form';
import Input from './input';
import HiddenField from './hidden';

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
    IReceiveProviderFields,
    IStore,
} from '../interfaces';
import {
    isMail,
    required,
} from '../validation';

interface IProps {
    accessKey: string;
    required: boolean;
}

interface IState {
    personId?: { hasValue: boolean; value: string };
}

class PersonProvider extends React.Component<IProps & IState, {}> {

    public render() {
        const {children, personId} = this.props;
        const personIdField = (<Field
            name={'personId'}
            validate={this.props.required ? [required] : []}
            component={HiddenField}
            providerOptions={personId}
        />);
        if (this.props.personId) {
            return <div>
                {children}
            </div>;
        } else {
            return <>
                {personIdField}
                <Input accessKey={this.props.accessKey}/>
            </>;
        }
    }
}

const mapDispatchToProps = (): IState => {
    return {};
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
