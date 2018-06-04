import * as React from 'react';
import { Async } from 'react-select';
import { WrappedFieldProps } from 'redux-form';
import { netteFetch } from '../../../../../fetch-api/middleware/fetch';
import {
    IRequestData,
    ISchool,
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
        const renderer = (v: ISchool) => {
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
            return netteFetch<IRequestData, ISchool[]>({
                act: 'school-provider',
                data: {
                    payload: input,
                },
            }, (response) => {
                cb(null, {
                    complete: true,
                    options: response.data,
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
