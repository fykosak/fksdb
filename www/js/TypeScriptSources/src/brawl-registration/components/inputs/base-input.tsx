import * as React from 'react';
import { WrappedFieldProps } from 'redux-form';
import ErrorDisplay from './error-display';
import { connect } from 'react-redux';
import {
    IProviderValue,
    IStore,
} from '../../../person-provider/interfaces';

export interface IBaseInputProps {
    accessKey: string;
    inputType: string;
    readOnly: boolean;
    placeholder?: string;
    JSXDescription?: JSX.Element;
    JSXLabel: JSX.Element;
    noChangeMode: boolean;
}

interface IState {
    providerProperty?: IProviderValue<any>;
}

class BaseInput extends React.Component<WrappedFieldProps & IBaseInputProps & IState, {}> {

    public render() {
        const {
            input,
            inputType,
            readOnly,
            meta,
            meta: {invalid, touched},
            JSXDescription,
            JSXLabel,
            noChangeMode,
            providerProperty,
        } = this.props;

        return <div className="form-group">
            <label>{JSXLabel}</label>
            {JSXDescription && (<small className="form-text text-muted">{JSXDescription}</small>)}
            <input
                className={'form-control' + (touched && invalid ? ' is-invalid' : '')}
                readOnly={readOnly || (noChangeMode && providerProperty && providerProperty.hasValue)}
                {...input}
                type={inputType}
            />
            <ErrorDisplay input={input} meta={meta}/>
        </div>;
    }
}

const mapDispatchToProps = (): IState => {
    return {};
};

const mapStateToProps = (state: IStore, ownProps: WrappedFieldProps & IBaseInputProps): IState => {

    const {accessKey, input: {name}} = ownProps;
    if (state.provider.hasOwnProperty(accessKey)) {
        return {
            providerProperty: state.provider[accessKey].fields[name],
        };
    }
    return {};
};

export default connect(mapStateToProps, mapDispatchToProps)(BaseInput);
