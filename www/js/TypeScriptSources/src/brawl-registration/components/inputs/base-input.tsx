import * as React from 'react';
import { WrappedFieldProps } from 'redux-form';
import { IProviderValue } from '../../../person-provider/reducers/provider';

interface IProps {
    type: string;
    readOnly: boolean;
    providerOptions: IProviderValue;
}

export default class BaseInput extends React.Component<WrappedFieldProps & IProps, {}> {

    public componentDidMount() {
        if (this.props.providerOptions.hasValue) {
            this.props.input.onChange(this.props.providerOptions.value);
        }
    }

    public render() {
        const {
            input,
            type,
            readOnly,
        } = this.props;

        return <input className="form-control" readOnly={readOnly} {...input} type={type}/>;
    }
}
