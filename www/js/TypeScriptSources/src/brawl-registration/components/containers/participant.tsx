import * as React from 'react';
import { connect } from 'react-redux';
import {
    FormSection,
} from 'redux-form';
import { getAccommodationFromState } from '../../middleware/price';
import Accommodation from '../accommodation';
import { FORM_NAME } from '../form';

import BaseInput from '../inputs/base-input';
import Input from '../inputs/input';

import Schedule from '../schedule';
import SchoolField from '../school-provider';
import StudyYearField from '../inputs/study-year';
import { getFieldName } from './persons';

import Price from './price';

interface IState {
    acc?: any;
    onSubmitFail?: (e) => void;
    onSubmitStart?: () => void;
    onSubmitSuccess?: (data) => void;
    providerOpt?: {
        accommodation?: { hasValue: boolean; value: string };
        email?: { hasValue: boolean; value: string };
        familyName?: { hasValue: boolean; value: string };
        idNumber?: { hasValue: boolean; value: string };
        otherName?: { hasValue: boolean; value: string };
        personId?: { hasValue: boolean; value: string };
        school?: { hasValue: boolean; value: string };
        studyYear?: { hasValue: boolean; value: string };
    };
}

interface IProps {
    type: string;
    index: number;
}

class ParticipantForm extends React.Component<IState & IProps, {}> {
    public render() {
        const {providerOpt: {otherName, familyName, email, idNumber, school, studyYear}, acc, type, index} = this.props;
        let hasAccommodation = false;
        for (const date in acc) {
            if (acc.hasOwnProperty(date)) {
                hasAccommodation = hasAccommodation || acc[date];
            }
        }

        return <>
            <div>
                <h3>Base info</h3>
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
            </div>
            <div>
                <h3>School</h3>
                <Input label={'School'}
                       type={null}
                       secure={true}
                       component={SchoolField}
                       modifiable={true}
                       name={'school'}
                       providerOptions={school}
                />
                <Input label={'Study year'}
                       type={null}
                       secure={true}
                       component={StudyYearField}
                       modifiable={true}
                       name={'studyYear'}
                       providerOptions={studyYear}
                />
            </div>

            <div>
                <h3>Accommodation</h3>
                <FormSection name={'accommodation'}>
                    <Accommodation type={this.props.type} index={this.props.index}/>
                </FormSection>
                {hasAccommodation && (
                    <Input label={'Číslo OP/pasu'}
                           type={'text'}
                           secure={true}
                           description={'Kvôli ubytovaniu.'}
                           component={BaseInput}
                           modifiable={true}
                           name={'idNumber'}
                           providerOptions={idNumber}
                    />)
                }
            </div>
            <div>
                <h3>Schedule</h3>
                <FormSection name={'schedule'}>
                    <Schedule type={this.props.type} index={this.props.index}/>
                </FormSection>
            </div>
            <Price type={type} index={index}/>
        </>;
    }
}

// <div>
//                 <h3>School</h3>
//                 <Input label={'School'}
//                        type={null}
//                        secure={true}
//                        component={SchoolField}
//                        modifiable={true}
//                        name={'school'}
//                        providerOptions={school}
//                 />
//                 <Input label={'Study year'}
//                        type={null}
//                        secure={true}
//                        component={StudyYearField}
//                        modifiable={true}
//                        name={'studyYear'}
//                        providerOptions={studyYear}
//                 />
//             </div>

const mapDispatchToProps = (): IState => {
    return {};
};

const mapStateToProps = (state, ownProps: IProps): IState => {
    const accessKey = getFieldName(ownProps.type, ownProps.index);
    if (state.provider.hasOwnProperty(accessKey)) {
        // const fieldNames = ['personId', 'email', 'school', 'studyYear', 'accommodation', 'idNumber', 'familyName', 'otherName'];
        return {
            ...getAccommodationFromState(FORM_NAME, state, ownProps),
            providerOpt: {
                accommodation: state.provider[accessKey].accommodation,
                email: state.provider[accessKey].email,
                familyName: state.provider[accessKey].familyName,
                idNumber: state.provider[accessKey].idNumber,
                otherName: state.provider[accessKey].otherName,
                personId: state.provider[accessKey].personId,
                school: state.provider[accessKey].school,
                studyYear: state.provider[accessKey].studyYear,
            },
        };
    }
    return {};
};

export default connect(mapStateToProps, mapDispatchToProps)(ParticipantForm);
