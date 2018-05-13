import * as React from 'react';

interface IProps {
    label: boolean;
    type: string;
    hasValue: boolean;
    storedValue: string;
}

export default class Input extends React.Component<IProps & any, {}> {

    public componentDidMount() {
        if (this.props.hasValue) {
            this.props.input.onChange(this.props.storedValue);
        }
    }

    public render() {
        const {
            meta: {touched, error, warning},
            input,
            label,
            type,
            readOnly,
            modifiable,
        } = this.props;

        return <div className="form-group">
            <label>{label}</label>
            <div className={modifiable ? 'input-group' : ''}>
                <input readOnly={readOnly} className="form-control" {...input} type={type}/>
                {modifiable && (<div className="input-group-apend">
                    <button className="btn btn-warning" type="button">Clear</button>
                </div>)}
            </div>
            {touched &&
            ((error && <span>{error}</span>) ||
                (warning && <span>{warning}</span>))}
        </div>;
    }
}
