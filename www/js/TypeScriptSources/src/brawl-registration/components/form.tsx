import * as React from 'react';
import { connect } from 'react-redux';
import {
    Field,
    Form,
    InjectedFormProps,
    reduxForm,
} from 'redux-form';
import { netteFetch } from '../../fetch-api/middleware/fetch';
import {
    IRequest,
    IResponse,
} from '../../fetch-api/middleware/interfaces';
import {
    required,
} from '../../person-provider/validation';
import { IStore } from '../reducers';
import PersonsContainer from './containers/persons';
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

interface ITeamNameRequest extends IRequest {
    name: string;
}

const asyncValidate = (values, dispatch) => {
    console.log(values);
    return new Promise((resolve) => {

        netteFetch<ITeamNameRequest, IResponse<ITeamNameResponse>>({
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
