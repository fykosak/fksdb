import * as React from 'react';
import {
    Field,
    FieldArray,
    Form,
    reduxForm,
} from 'redux-form';
import TeamName from './inputs/team-name';

import PersonsContainer from './containers/persons';

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

class BrawlForm extends React.Component<any, any> {

    public render() {
        // const {valid, submitting, handleSubmit, onSubmit, tasks, teams} = this.props;

// handleSubmit(onSubmit)
        return (
            <Form onSubmit={() => {
                console.log('submit');
            }}>
                <Field name="teamName" component={TeamName}/>
                <FieldArray name="persons" component={PersonsContainer} f/>
            </Form>
        );
    }
}

const asyncValidate = (values, dispatch) => {
    console.log(values);
    return new Promise((resolve) => {

        setTimeout(resolve, 5000);
    });
};

export const FORM_NAME = 'brawlRegistrationForm';

export default reduxForm({
    asyncChangeFields: ['teamName'],
    asyncValidate,
    form: FORM_NAME,
    initialValues: {persons, teamName: "ahoj"},
    /* validate: () => {
         return {};
     },*/

})(BrawlForm);
