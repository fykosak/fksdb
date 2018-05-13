import * as React from 'react';

import { Async } from 'react-select';
import { netteFetch } from '../../../shared/helpers/fetch';
import { connect } from 'react-redux';
import { Dispatch } from 'redux';
import {
    ACTION_SUBMIT_START,
    submitFail,
    submitSuccess,
} from '../../../entry-form/actions';

class SchoolProvider extends React.Component<any, { value?: any }> {
    public render() {
        const {input: {onChange, value}} = this.props;

        return <div className="form-group">
            <label>School</label>
            <Async
                name="form-field-name"
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
            />
        </div>;
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
