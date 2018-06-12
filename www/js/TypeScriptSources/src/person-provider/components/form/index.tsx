import * as React from 'react';
import { Field } from 'redux-form';
import {
    isMail,
    required,
} from '../../validation';
import InputField from './field';
import HiddenField from '../../../brawl-registration/components/inputs/hidden';

interface IProps {
    accessKey: string;
}

export default class Form extends React.Component<IProps, {}> {

    public render() {
// validate={[required, isMail]}
        return <div className={'form-group'}>
            <Field
                name={'email'}
                component={InputField}
                accessKey={this.props.accessKey}
            />
        </div>;
    }

}
