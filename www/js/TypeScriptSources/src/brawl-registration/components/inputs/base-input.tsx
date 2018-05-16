import * as React from 'react';

export default class BaseInput extends React.Component<any, {}> {

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
