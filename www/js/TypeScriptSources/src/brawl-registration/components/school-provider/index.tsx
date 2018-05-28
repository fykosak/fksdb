import * as React from 'react';
import { Async } from 'react-select';
import { WrappedFieldProps } from 'redux-form';
import { netteFetch } from '../../../submit/middleware/fetch';
import { IResponse } from '../../../submit/middleware/interfaces';
import {
    ISchool,
    ISchoolProviderResponse,
} from './interfaces';

interface IProps {
    hasValue: boolean;
    storedValue: string;
}

export default class SchoolProvider extends React.Component<IProps & WrappedFieldProps, {}> {

    public componentDidMount() {
        if (this.props.hasValue) {
            this.props.input.onChange(this.props.storedValue);
        }
    }

    public render() {
        const {input: {onChange, value}} = this.props;
        const renderer = (v) => {
            return (<span>
                        <img style={{height: '1em'}}
                             className="mr-2"
                             alt=""
                             src={'/flags/4x3/' + v.region.toLowerCase() + '.svg'}
                        />{v.label}</span>
            );
        };

        const loadOptions = (input, cb) => {
            const netteJQuery: any = $;
            netteJQuery.nette.ext('unique', null);
            netteFetch<ISchoolProviderResponse, IResponse<ISchool[]>>({
                act: 'school-provider',
                payload: input,
            }, (data) => {
                cb(null, {
                    complete: true,
                    options: data.data,
                });
            }, (e) => {
                throw e;
            });
        };

        return <Async
            name="school-provider"
            value={value}
            onChange={onChange}
            loadOptions={loadOptions}
            optionRenderer={renderer}
            valueRenderer={renderer}
        />;
    }
}
