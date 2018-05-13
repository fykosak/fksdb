import * as React from 'react';
import {
    Field,
    Form,
    reduxForm,
} from 'redux-form';
import TeamName from './inputs/team-name';

import PersonsContainer, { getFieldName } from './containers/persons';
import { connect } from 'react-redux';

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
                <PersonsContainer/>
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

const mapDispatchToProps = () => {
    return {};
};

const mapStateToProps = (state) => {
    const data = {};
    for (const accessKey in state.provider) {
        if (state.provider.hasOwnProperty(accessKey)) {
            for (const name in state.provider[accessKey]) {
                if (state.provider[accessKey].hasOwnProperty(name)) {
                    data[accessKey + '.' + name] = state.provider[accessKey][name].value;
                }
            }
        }
    }
    return {
        initialValues: data,
    };

};

export default reduxForm({
    asyncChangeFields: ['teamName'],
    asyncValidate,
    form: FORM_NAME,
    // initialValues: {persons, teamName: "ahoj"},
    /* validate: () => {
         return {};
     },*/

})(connect(mapStateToProps, mapDispatchToProps)(BrawlForm));

