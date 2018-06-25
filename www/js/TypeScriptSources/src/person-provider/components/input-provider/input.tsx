import * as React from 'react';
import { WrappedFieldProps } from 'redux-form';

interface IProps {
    inputDef: {
        value: any;
        filled: boolean;
    };
}

export default class Input extends React.Component<WrappedFieldProps & IProps, {}> {

    public componentDidMount() {
        if (!this.props.inputDef.filled) {
            return;
        }
        const {inputDef: {value}, input: {onChange}} = this.props;
        onChange(value ? value : true);
    }

    public render() {
        return null;
    }
}
