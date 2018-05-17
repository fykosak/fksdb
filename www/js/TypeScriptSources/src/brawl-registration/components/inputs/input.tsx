import * as React from 'react';
import { Field } from 'redux-form';
import ErrorDisplay from './error-display';
import SecureDisplay from './secure-display';

interface IProps {
    label: string;
    type: string;
    secure: boolean;
    component: any;
    modifiable: boolean;
    placeholder?: string;
    name: string;
    providerOptions: {
        hasValue: boolean;
        value: string;
    };
}

export default class Input extends React.Component<IProps, {}> {

    public render() {
        const {
            label,
            secure,
            modifiable,
            providerOptions,
            type,
            placeholder,
            name,
            component,
        } = this.props;
        if (!name) {
            return null;
        }
        return <div className="form-group">
            <label>{label}</label>
            <div>
                {(secure && providerOptions.hasValue) ?
                    (<Field name={name}
                            component={SecureDisplay}
                            modifiable={modifiable}
                        />
                    ) :
                    (<Field providerOptions={providerOptions}
                            readOnly={!modifiable}
                            placeholder={placeholder}
                            type={type}
                            name={name}
                            component={component}
                        />
                    )
                }
            </div>
            <Field name={name} component={ErrorDisplay}/>
        </div>;
    }
}
