import * as React from 'react';
import {
    Field,
    Form,
    reduxForm,
} from 'redux-form';

import { connect } from 'react-redux';
import { IStore } from '../reducers';
import PersonsContainer, { getFieldName } from './containers/persons';

import { InjectedFormProps } from 'redux-form';
import BaseInput from './inputs/base-input';
import ErrorDisplay from './inputs/error-display';
import { netteFetch } from '../../shared/helpers/fetch';
import {
    required,
} from '../../person-provider/validation';
import { IReceiveData } from '../../shared/interfaces';
import TeamName from './inputs/team-name';

interface IState {
    initialValues?: any;
}

class BrawlForm extends React.Component<IState & InjectedFormProps & any, {}> {

    public render() {
        // const {valid, submitting, handleSubmit, onSubmit, tasks, teams} = this.props;
        const {handleSubmit} = this.props;
// handleSubmit(onSubmit)
        return (
            <Form onSubmit={handleSubmit((...args) => {
                console.log('submit');
            })}>
                <Field
                    validate={[required]}
                    name={'teamName'}
                    component={TeamName}
                />

                <PersonsContainer/>
                <button type='submit'>Submit</button>
            </Form>
        );
    }
}

interface ITeamNameResponse {
    result: boolean;
}

interface ITeamNameRequest {
    act: string;
    name: string;
}

const asyncValidate = (values, dispatch) => {
    console.log(values);
    return new Promise((resolve) => {

        netteFetch<ITeamNameRequest, IReceiveData<ITeamNameResponse>>({
            act: 'team-name-unique',
            name: values.teamName,
        }, (data) => {
            if (!data.data.result) {
                resolve({teamName: data.messages[0].text});
            }
        }, (e) => {
            throw e;
        });
    });
};

export const FORM_NAME = 'brawlRegistrationForm';

const mapDispatchToProps = (): IState => {
    return {};
};

const mapStateToProps = (state: IStore): IState => {
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
