import * as React from 'react';
import { connect } from 'react-redux';
import { Dispatch } from 'redux';
import { dispatchNetteFetch } from '../../fetch-api/middleware/fetch';
import { IRequest } from '../../fetch-api/middleware/interfaces';
import { AJAX_CALL_ACTION } from '../constants';
import { ILanguageResponseData } from '../interfaces';
import { ILangStore } from '../reducers';

interface IState {
    onLoad?: (data: IRequest<{}>, success: (d) => void, error: (e) => void) => void;
}

class Async extends React.Component<IState, {}> {

    public componentDidMount() {
        const {onLoad} = this.props;
        onLoad({act: AJAX_CALL_ACTION, data: {}}, () => null, () => null);
    }

    public render() {
        return null;
    }
}

const mapDispatchToProps = (dispatch: Dispatch<ILangStore>): IState => {
    return {
        onLoad: (data: IRequest<{}>, success, error) =>
            dispatchNetteFetch<{}, ILanguageResponseData, ILangStore>
            (AJAX_CALL_ACTION, dispatch, data, success, error),
    };
};

const mapStateToProps = (): IState => {
    return {};
};

export default connect(mapStateToProps, mapDispatchToProps)(Async);
