import * as React from 'react';
import { connect } from 'react-redux';
import {
    Field,
    FormSection,
} from 'redux-form';
import { getAccommodationFromState } from '../../middleware/price';
import Accommodation from '../accommodation';
import { FORM_NAME } from '../form';

import Input from '../inputs/input';
import BaseInput from '../inputs/base-input';

import SchoolField from '../school-provider';
import { getFieldName } from './persons';
import Schedule from '../schedule';

interface IState {
    acc?: any;
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
        const {otherName, familyName, email, idNumber, acc, school} = this.props;
        let hasAccommodation = false;
        for (const date in acc) {
            if (acc.hasOwnProperty(date)) {
                hasAccommodation = hasAccommodation || acc[date];
            }
        }

        return <>

            <Input name={'otherName'}
                   label={'Other name'}
                   type={'text'}
                   component={BaseInput}
                   placeholder={'Name'}
                   providerOptions={otherName}
                   modifiable={true}
                   secure={false}
            />

            <Input name={'familyName'}
                   label={'Family name'}
                   type={'text'}
                   component={BaseInput}
                   placeholder={'Name'}
                   providerOptions={familyName}
                   modifiable={true}
                   secure={false}
            />
            <Input
                name={'email'}
                label={'E-mail'}
                type={'email'}
                component={BaseInput}
                placeholder={'youmail@example.com'}
                providerOptions={email}
                modifiable={false}
                secure={false}
            />

            <Input label={'School'}
                   type={null}
                   secure={true}
                   component={SchoolField}
                   modifiable={true}
                   name={'school'}
                   providerOptions={school}
            />

            <FormSection name={'accommodation'}>
                <Accommodation type={this.props.type} index={this.props.index}/>

            </FormSection>
            {hasAccommodation && (
                <Input label={'Číslo OP/pasu'}
                       type={'text'}
                       secure={true}
                       component={BaseInput}
                       modifiable={true}
                       name={'idNumber'}
                       providerOptions={idNumber}
                />)

            }
            <FormSection name={'schedule'}>
                <Schedule type={this.props.type} index={this.props.index}/>
            </FormSection>
        </>;
    }
}

// <Field name={'school'}>
//                 <SchoolField
//                     storedValue={school.value}
//                     hasValue={school.hasValue}/>
//             </Field>
//
//

//

const mapDispatchToProps = (): IState => {
    return {};
};

const mapStateToProps = (state, ownProps: IProps): IState => {
    const acc = getAccommodationFromState(FORM_NAME, state, ownProps);

    const accessKey = getFieldName(ownProps.type, ownProps.index);
    if (state.provider.hasOwnProperty(accessKey)) {
        // const fieldNames = ['personId', 'email', 'school', 'studyYear', 'accommodation', 'idNumber', 'familyName', 'otherName'];
        return {
            ...acc,
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
