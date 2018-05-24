import * as React from 'react';
import { Field } from 'redux-form';
import ErrorDisplay from './error-display';
import SecureDisplay from './secure-display';
import { IProviderValue } from '../../../person-provider/reducers/provider';
import { required as requiredTest } from '../../../person-provider/validation';

interface IProps {
    label: string;
    type: string;
    secure: boolean;
    component: any;
    modifiable: boolean;
    placeholder?: string;
    description?: string;
    required: boolean;
    name: string;
    providerOptions: IProviderValue;
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
            description,
            required,
        } = this.props;
        if (!name) {
            return null;
        }
        return <div className="form-group">
            <label>{label}</label>
            {description && (<small className="form-text text-muted">{description}</small>)}

            {(secure && providerOptions.hasValue) ?
                (<Field name={name}
                        component={SecureDisplay}
                        modifiable={modifiable}
                    />
                ) :
                (<>
                        <Field providerOptions={providerOptions}
                               readOnly={!modifiable}
                               placeholder={placeholder}
                               type={type}
                               name={name}
                               component={component}
                               required={required}
                               validate={(required) ? [requiredTest] : []}
                        />
                        <Field name={name}
                               component={ErrorDisplay}
                               validate={(required) ? [requiredTest] : []}
                        />
                    </>
                )
            }
        </div>;
    }
}
