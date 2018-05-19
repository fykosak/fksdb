import * as React from 'react';
import {
    Field,
    Form,
    reduxForm,
} from 'redux-form';
import TeamName from './inputs/team-name';

import { connect } from 'react-redux';
import PersonsContainer, { getFieldName } from './containers/persons';
import { IStore } from '../reducers';

class BrawlForm extends React.Component<any, {}> {

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

const mapDispatchToProps = (): any => {
    return {};
};

const mapStateToProps = (state: IStore): any => {
    return {
        initialValues: null,
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
