import * as React from 'react';
import { WrappedFieldProps } from 'redux-form';
import Button from './button';
import Input from './input';

interface IProps {
    accessKey: string;
}

export default class Field extends React.Component<IProps & WrappedFieldProps, {}> {
    public render() {
        const {input, meta, accessKey} = this.props;

        return <>
            <Input input={input} meta={meta}/>
            <Button accessKey={accessKey} value={this.props.input.value} meta={meta} input={input}/>
        </>;
    }
}
