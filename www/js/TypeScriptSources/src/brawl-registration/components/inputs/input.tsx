import * as React from 'react';
import { Field } from 'redux-form';
import ErrorDisplay from './error-display';
import SecureDisplay from './secure-display';
import { IProviderValue } from '../../../person-provider/reducers/provider';

interface IProps {
    label: string;
    type: string;
    secure: boolean;
    component: any;
    modifiable: boolean;
    placeholder?: string;
    description?: string;
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
        } = this.props;
        if (!name) {
            return null;
        }
        return <div className="form-group">
            <label>{label}</label>
            {description && (<small className="form-text text-muted">{description}</small>)}
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
