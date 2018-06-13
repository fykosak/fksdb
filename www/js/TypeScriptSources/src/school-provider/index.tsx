import * as React from 'react';
import { Async } from 'react-select';
import { WrappedFieldProps } from 'redux-form';
import ErrorDisplay from '../../../inputs/error-display';
import { ISchool } from './interfaces';
import { loadOptions } from './middleware';
import Option from './option';

export interface ISchoolProviderInputProps {
    JSXLabel: JSX.Element;
    JSXDescription?: JSX.Element;
}

export default class SchoolProvider extends React.Component<ISchoolProviderInputProps & WrappedFieldProps, {}> {

    public render() {
        const {
            input: {onChange, value},
            meta,
            JSXLabel,
            JSXDescription,
            input,
        } = this.props;
        const renderer = (v: ISchool) => {
            return (<Option label={v.label} region={v.region}/>);
        };

        return <div className="form-group">
            <label>{JSXLabel}</label>
            {JSXDescription && (<small className="form-text text-muted">{JSXDescription}</small>)}
            <Async
                name="school-provider"
                value={value}
                onChange={onChange}
                loadOptions={loadOptions}
                optionRenderer={renderer}
                valueRenderer={renderer}
            />
            <ErrorDisplay input={input} meta={meta}/>
        </div>;
    }
}
