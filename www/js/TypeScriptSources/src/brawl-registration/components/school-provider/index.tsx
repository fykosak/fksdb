import * as React from 'react';

import { connect } from 'react-redux';
import { Async } from 'react-select';
import { Dispatch } from 'redux';
import {
    ACTION_SUBMIT_START,
    submitFail,
    submitSuccess,
} from '../../../entry-form/actions';
import { netteFetch } from '../../../shared/helpers/fetch';
import { IReceiveData } from '../../../shared/interfaces';

import { WrappedFieldProps } from 'redux-form';
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
            netteFetch<ISchoolProviderResponse, IReceiveData<ISchool[]>>({
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
