import * as React from 'react';
import {
    Field,
    Fields as FieldsComponent,
    getFormValues,
    formValueSelector,
    FormSection,
} from 'redux-form';
import { netteFetch } from '../../../shared/helpers/fetch';
import { connect } from 'react-redux';
import { IStore } from '../../../results/reducers';

const fieldNames = ['personId', 'email', 'school', 'studyYear', 'accommodation', 'idNumber', 'familyName', 'otherName'];

class ParticipantForm extends React.Component<any, {}> {
    public render() {
        return null;
    }
}

class TeacherForm extends React.Component<any, {}> {
    public render() {
        return null;
    }
}

interface IProps {
    type: string;
    index: number;
}

const isMail = (value: string): string => {
    return /^[a-zA-Z0-9.!#$%&’*+/=?^_`{|}~-]+@[a-zA-Z0-9-]+(?:\.[a-zA-Z0-9-]+)*$/.test(value) ? undefined : 'is not a valid Mail';
};
const required = (value): string => {
    return (value ? undefined : 'Required');
};

class PersonProvider extends React.Component<IProps, {}> {

    public render() {
        const personId = null;
        return <div className={'tab-pane fade show' + (this.props.index ? '' : ' active')}
                    id={'member' + this.props.index}
                    role="tabpanel"
                    aria-labelledby={'member' + this.props.index + '-tab'}>
            <Field name={'provider'}
                   component={PersonProviderInput}
                   validate={[required, isMail]}
            />
            {personId && <div>

            </div>}
        </div>;
    }
}

class PersonProviderInput extends React.Component<any, {}> {

    public render() {
        const {meta: {error, touched, valid, warning}, input} = this.props;
        return <div>
            <input {...input} type="email" required="required"/>
            {touched &&
            ((error && <span>{error}</span>) ||
                (warning && <span>{warning}</span>))}
            <button disabled={!valid} onClick={(event) => {
                event.preventDefault();
                netteFetch({
                    email: input.value,
                    fields: [],
                }, () => {
                }, () => {
                });
            }}>Search
            </button>
        </div>;
    }
}

const persons = [
    {
        personId: null,
        type: 'participant',
    },
    {
        personId: null,
        type: 'participant',
    },
    {
        personId: null,
        type: 'participant',
    },
    {
        personId: null,
        type: 'participant',
    },
    {
        personId: null,
        type: 'participant',
    },
    {
        personId: null,
        type: 'teacher',
    },
];

const getFieldName = (member, index: number): string => {
    return member.type + '[' + index + ']';
};

export default class PersonsContainer extends React.Component<any, {}> {
    public render() {
        const {fields, meta: {error, submitFailed}} = this.props;
        const tabs = persons.map((member, index) => {
            let form = null;

            switch (member.type) {
                default:
                case 'participant':
                    form = <ParticipantForm/>;
                    break;
                case 'teacher':
                    form = <TeacherForm/>;

            }
            return <FormSection key={index} name={getFieldName(member, index)}>
                <PersonProvider index={index} type={member.type}>
                    {form}
                </PersonProvider>
            </FormSection>;
        });

        return <div>
            <ul className="nav nav-tabs" id="myTab" role="tablist">
                {persons.map((member, index) => {
                    return <li className="nav-item">
                        <a className={'nav-link' + (index ? '' : ' active')}
                           id={'#member' + index + '-tab'}
                           data-toggle="tab"
                           href={'#member' + index} role="tab"
                           aria-controls={'member' + index}
                           aria-selected="true">{index + 1} {member.type}</a>
                    </li>;
                })}
            </ul>
            <div className="tab-content" id="myTabContent">
                {tabs}
            </div>
        </div>;
    }
}
