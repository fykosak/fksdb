import * as React from 'react';
import { WrappedFieldProps } from 'redux-form';
import { IInputProps } from '../../../person-provider/components/input-provider';

export default class HiddenField extends React.Component<WrappedFieldProps & IInputProps, {}> {

    public render() {
        const {input} = this.props;

        return <input {...input} type="hidden"/>;
    }
}
