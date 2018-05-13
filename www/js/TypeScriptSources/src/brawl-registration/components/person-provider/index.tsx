import * as React from 'react';
import {
    Field,
    FormSection,
} from 'redux-form';
import Input from './input';

import { connect } from 'react-redux';
import {
    ACTION_SUBMIT_START,
    submitFail,
    submitSuccess,
} from '../../../entry-form/actions';
import { Dispatch } from 'redux';
import { getFieldName } from '../containers/persons';

interface IProps {
    type: string;
    index: number;
}

interface IState {
    onSubmitFail?: (e) => void;
    onSubmitStart?: () => void;
    onSubmitSuccess?: (data) => void;
    accommodation?: { hasValue: boolean; value: string };
    email?: { hasValue: boolean; value: string };
    familyName?: { hasValue: boolean; value: string };
    idNumber?: { hasValue: boolean; value: string };
    otherName?: { hasValue: boolean; value: string };
    personId?: { hasValue: boolean; value: string };
    school?: { hasValue: boolean; value: string };
    studyYear?: { hasValue: boolean; value: string };
}

const isMail = (value: string): string => {
    return /^[a-zA-Z0-9.!#$%&’*+/=?^_`{|}~-]+@[a-zA-Z0-9-]+(?:\.[a-zA-Z0-9-]+)*$/.test(value) ? undefined : 'is not a valid Mail';
};
const required = (value): string => {
    return (value ? undefined : 'Required');
};

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
                       onSubmitError={(e) => {
                           this.props.onSubmitFail(e);
                       }}
                       onSubmitStart={() => {
                           this.props.onSubmitStart();
                       }}
                       onSubmitSuccess={(data) => {
                           data.key = getFieldName(this.props.type, this.props.index);
                           this.props.onSubmitSuccess(data);
                       }}
                />
            </>;
        }
    }
}

const submitStart = () => {
    return {
        type: ACTION_SUBMIT_START,
    };

};

const mapDispatchToProps = (dispatch: Dispatch<any>): IState => {
    return {
        onSubmitFail: (e) => dispatch(submitFail(e)),
        onSubmitStart: () => dispatch(submitStart()),
        onSubmitSuccess: (data) => dispatch(submitSuccess(data)),
    };
};

const mapStateToProps = (state, ownProps: IProps): IState => {
    const accessKey = getFieldName(ownProps.type, ownProps.index);
    if (state.provider.hasOwnProperty(accessKey)) {
        // const fieldNames = ['personId', 'email', 'school', 'studyYear', 'accommodation', 'idNumber', 'familyName', 'otherName'];
        return {
            personId: state.provider[accessKey].personId,
        };
    }
    return {};
};

export default connect(mapStateToProps, mapDispatchToProps)(PersonProvider);
