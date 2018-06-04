import * as React from 'react';
import { WrappedFieldProps } from 'redux-form';
import { IInputProps } from './input';

export default class HiddenField extends React.Component<WrappedFieldProps & IInputProps, {}> {

    public componentDidMount() {
        if (this.props.providerOptions && this.props.providerOptions.hasValue) {
            this.props.input.onChange(this.props.providerOptions.value);
        }
    }

    public render() {
        const {input} = this.props;

        return <input {...input} type="hidden"/>;
    }
}
