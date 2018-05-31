import * as React from 'react';
import { connect } from 'react-redux';
import {
    Form,
    InjectedFormProps,
    reduxForm,
} from 'redux-form';
import { IStore } from '../../reducers';
import PersonsContainer from '../containers/persons';
import TeamName from './sections/team-name/';
import { asyncValidate } from './sections/team-name/validate';

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
                <TeamName/>
                <PersonsContainer/>
                <button type='submit'>Submit</button>
            </Form>
        );
    }
}

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
