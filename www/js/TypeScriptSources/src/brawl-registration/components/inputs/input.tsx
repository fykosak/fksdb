import * as React from 'react';
import { Field } from 'redux-form';
import { IProviderValue } from '../../../person-provider/interfaces';
import { required as requiredTest } from '../../../person-provider/validation';
import SecureDisplay from './secure-display';

interface IProps {
    label: JSX.Element;
    type: string;
    secure: boolean;
    component: any;
    modifiable: boolean;
    placeholder?: string;
    description?: JSX.Element;
    required: boolean;
    name: string;
    providerOptions: IProviderValue<any>;
    removeProviderValue?: () => void;
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
            removeProviderValue,
        } = this.props;
        if (!name) {
            return null;
        }
        if (secure && providerOptions.hasValue) {
            return <Field
                removeProviderValue={removeProviderValue}
                name={name}
                component={SecureDisplay}
                JSXLabel={label}
            />;
        }
        return <Field
            providerOptions={providerOptions}
            readOnly={!modifiable}
            placeholder={placeholder}
            type={type}
            name={name}
            JSXLabel={label}
            description={description}
            component={component}
            required={required}
            validate={(required) ? [requiredTest] : []}
        />;
    }
}

export interface IInputProps {
    type?: string;
    readOnly?: boolean;
    placeholder?: string;
    providerOptions?: IProviderValue<any>;
    JSXLabel: JSX.Element;
    description?: JSX.Element;
    removeProviderValue?: () => void;
}
