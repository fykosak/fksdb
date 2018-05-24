import * as React from 'react';
import { WrappedFieldProps } from 'redux-form';
import { IProviderValue } from '../reducers/provider';

interface IProps {
    providerOptions?: IProviderValue;
}

export default class HiddenField extends React.Component<WrappedFieldProps & IProps, {}> {

    public componentDidMount() {
        if (this.props.providerOptions && this.props.providerOptions.hasValue) {
            this.props.input.onChange(this.props.providerOptions.value);
        }
    }

    public render() {
        const {input} = this.props;

        return <input {...input} type="hidden"
        />;
    }
}
