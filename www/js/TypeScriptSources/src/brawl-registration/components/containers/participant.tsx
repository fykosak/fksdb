import * as React from 'react';
import { connect } from 'react-redux';
import {
    Field,
    FormSection,
} from 'redux-form';
import BaseInput from '../inputs/input';
import { getFieldName } from './persons';
import SchoolField from '../school-provider';
import Accommodation from '../accommodation';

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

interface IProps {
    type: string;
    index: number;
}

class ParticipantForm extends React.Component<IState & IProps, {}> {
    public render() {
        const {otherName, familyName, email} = this.props;
        return <>

            <Field name={'otherName'}
                   component={BaseInput}
                   label={'Other name'}
                   placeholder={'other name'}
                   type={'text'}
                   modifiable={true}
                   readOnly={otherName.hasValue ? 'readonly' : false}
                   storedValue={otherName.value}
                   hasValue={otherName.hasValue}
            />
            <Field name={'familyName'}
                   component={BaseInput}
                   label={'Family name'}
                   placeholder={'family name'}
                   type={'text'}
                   modifiable={true}
                   storedValue={familyName.value}
                   hasValue={familyName.hasValue}
                   readOnly={familyName.hasValue ? 'readonly' : false}
            />
            <Field name={'email'}
                   component={BaseInput}
                   readOnly="readonly"
                   label={'E-mail'}
                   placeholder={'youmail@example.com'}
                   type={'email'}
                   storedValue={email.value}
                   hasValue={email.hasValue}
            />
            <FormSection name={'accommodation'}>
                <Accommodation type={this.props.type} index={this.props.index}/>
            </FormSection>
        </>;
    }
}

// <Field name={'school'} component={SchoolField}/>

const mapDispatchToProps = (): IState => {
    return {};
};

const mapStateToProps = (state, ownProps: IProps): IState => {
    const accessKey = getFieldName(ownProps.type, ownProps.index);
    if (state.provider.hasOwnProperty(accessKey)) {
        // const fieldNames = ['personId', 'email', 'school', 'studyYear', 'accommodation', 'idNumber', 'familyName', 'otherName'];
        return {
            accommodation: state.provider[accessKey].accommodation,
            email: state.provider[accessKey].email,
            familyName: state.provider[accessKey].familyName,
            idNumber: state.provider[accessKey].idNumber,
            otherName: state.provider[accessKey].otherName,
            personId: state.provider[accessKey].personId,
            school: state.provider[accessKey].school,
            studyYear: state.provider[accessKey].studyYear,
        };
    }
    return {};
};

export default connect(mapStateToProps, mapDispatchToProps)(ParticipantForm);
