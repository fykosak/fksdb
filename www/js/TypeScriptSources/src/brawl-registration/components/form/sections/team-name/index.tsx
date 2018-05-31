import * as React from 'react';
import {
    Field,
} from 'redux-form';
import { required } from '../../../../../person-provider/validation';
import Container from './container';

export default class Index extends React.Component<{}, {}> {
    public render() {
        return <Field
            validate={[required]}
            name={'teamName'}
            component={Container}
        />;
    }
}
