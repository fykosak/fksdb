import * as React from 'react';
import { connect } from 'react-redux';
import {
    IFetchApiState,
} from '../../fetch-api/reducers';
import { IMessage } from '../interfaces';
import { IState as IErrorLoggerState } from '../reducers/error-logger';

interface IState {
    messages?: IMessage[];
}

interface IProps {
    accessKey: string;
}

class MessagesDisplay extends React.Component<IState & IProps, {}> {
    public render() {
        const {messages} = this.props;
        return <>{messages.map((message, index) => {
            return (<div key={index} className={'react-message alert alert-' + message.level}> {message.text}</div>);
        })}</>;
    }
}

interface IStore {
    fetchApi: IFetchApiState;
    errorLogger: IErrorLoggerState;
}

const mapStateToProps = (state: IStore, ownProps: IProps): IState => {
    const messages = state.fetchApi.hasOwnProperty(ownProps.accessKey) ? state.fetchApi[ownProps.accessKey].messages : [];
    return {
        messages: [
            ...messages,
            ...state.errorLogger.errors],
    };
};
const mapDispatchToProps = (): IState => {
    return {};
};

export default connect(mapStateToProps, mapDispatchToProps)(MessagesDisplay);
