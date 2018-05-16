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

interface IProps {
    hasValue: boolean;
    storedValue: string;
}

class SchoolProvider extends React.Component<IProps & any, {}> {

    public componentDidMount() {
        if (this.props.hasValue) {
            this.props.input.onChange(this.props.storedValue);
        }
    }

    public render() {
        const {input: {onChange, value}} = this.props;

        return <Async
            name="school-provider"
            value={value}
            onChange={onChange}
            loadOptions={(input, cb) => {
                $.nette.ext('unique', null);
                netteFetch({
                    act: 'school-provider',
                    payload: input,
                }, (data) => {
                    cb(null, {
                        complete: true,
                        options: data,
                    });
                }, (e) => {
                });
            }}
        />;
    }

}

const mapStateToProps = () => {
    return {};
};

const mapDispatchToProps = (dispatch: Dispatch<any>) => {
    return {
        onSubmitFail: (e) => dispatch(submitFail(e)),
        onSubmitStart: () => dispatch({type: ACTION_SUBMIT_START}),
        onSubmitSuccess: (data) => dispatch(submitSuccess(data)),
    };
};
export default connect(mapStateToProps, mapDispatchToProps)(SchoolProvider);
