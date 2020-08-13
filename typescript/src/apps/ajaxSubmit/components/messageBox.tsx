import { Message } from '@fetchApi/interfaces';
import { FetchApiState } from '@fetchApi/reducer';
import * as React from 'react';
import { connect } from 'react-redux';
import { State as ErrorLoggerState } from '../reducers/errorLogger';

interface StateProps {
    messages: Message[];
}

interface OwnProps {
    accessKey: string;
}

class MessageBox extends React.Component<StateProps & OwnProps, {}> {
    public render() {
        const {messages} = this.props;
        return <>{messages.map((message, index) => {
            return (<div key={index} className={'react-message alert alert-' + message.level}> {message.text}</div>);
        })}</>;
    }
}

interface Store {
    fetchApi: FetchApiState;
    errorLogger: ErrorLoggerState;
}

const mapStateToProps = (state: Store, ownProps: OwnProps): StateProps => {
    const messages = state.fetchApi.hasOwnProperty(ownProps.accessKey) ? state.fetchApi[ownProps.accessKey].messages : [];
    return {
        messages: [
            ...messages,
            ...state.errorLogger.errors,
        ],
    };
};

export default connect(mapStateToProps, null)(MessageBox);
