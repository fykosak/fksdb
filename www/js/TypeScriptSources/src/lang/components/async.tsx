import * as React from 'react';
import { connect } from 'react-redux';
import { Dispatch } from 'redux';
import { dispatchNetteFetch } from '../../submit/middleware/fetch';
import {
    IRequest,
    IResponse,
} from '../../submit/middleware/interfaces';
import { AJAX_CALL_ACTION } from '../constants';
import { ILangStore } from '../reducers';

interface IState {
    onLoad?: (data: IRequest, success: (d) => void, error: (e) => void) => void;
}

interface ILanguageResponseData {
    [key: string]: string;
}

class Async extends React.Component<IState, {}> {

    public componentDidMount() {
        const {onLoad} = this.props;
        onLoad({act: AJAX_CALL_ACTION}, () => {
            return;
        }, () => {
            return;

        });
    }

    public render() {
        return null;
    }

}

const mapDispatchToProps = (dispatch: Dispatch<ILangStore>): IState => {
    return {
        onLoad: (data: IRequest, success, error) =>
            dispatchNetteFetch<IRequest, IResponse<ILanguageResponseData>, ILangStore>
            (AJAX_CALL_ACTION, dispatch, data, success, error),
    };
};

const mapStateToProps = (): IState => {
    return {};
};

export default connect(mapStateToProps, mapDispatchToProps)(Async);
