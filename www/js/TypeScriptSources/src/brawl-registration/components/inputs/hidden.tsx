import * as React from 'react';
import { WrappedFieldProps } from 'redux-form';

export default class HiddenField extends React.Component<WrappedFieldProps, {}> {

    public render() {
        const {input} = this.props;

        return <input {...input} type="hidden"/>;
    }
}
