import * as React from 'react';
import { connect } from 'react-redux';
import { Dispatch } from 'redux';
import { WrappedFieldProps } from 'redux-form';
import { dispatchNetteFetch } from '../../../fetch-api/middleware/fetch';
import Lang from '../../../lang/components/lang';
import {
    IRequestData,
    IResponseData,
    IStore,
} from '../../interfaces';
import {
    isMail,
    required,
} from '../../validation';

interface IProps {
    accessKey: string;
    value: string;
}

interface IState {
    submitting?: boolean;
    onFindButtonClick?: (value: string) => Promise<any>;
}

const findButtonClick = (dispatch: Dispatch<IStore>, value: string, accessKey: string) => {

    return dispatchNetteFetch<IRequestData, IResponseData, IStore>('personProvider/' + accessKey, dispatch, {
        act: 'person-provider',
        data: {
            accessKey,
            email: value,
            fields: [],
        },
    });
};

class Field extends React.Component<IProps & IState & WrappedFieldProps, {}> {
    public render() {
        const {submitting, onFindButtonClick} = this.props;

        const valid = ![required, isMail].reduce((init, test) => {
            return init || test(this.props.value);
        }, '');
        return <button className="btn btn-primary" disabled={submitting || !valid} onClick={(event) => {
            event.preventDefault();
            return onFindButtonClick(this.props.value);
        }}>
            <Lang text={'search'}/>
        </button>;
    }
}

const mapDispatchToProps = (dispatch: Dispatch<IStore>, ownProps: IProps): IState => {
    return {
        onFindButtonClick: (value) => findButtonClick(dispatch, value, ownProps.accessKey),
    };
};

const mapStateToProps = (state: IStore, ownProps: IProps): IState => {
    const key = 'personProvider/' + ownProps.accessKey;

    if (state.submit.hasOwnProperty(key)) {
        return {
            submitting: state.submit[key].submitting,
        };
    }
    return {};
};

export default connect(mapStateToProps, mapDispatchToProps)(Field);
