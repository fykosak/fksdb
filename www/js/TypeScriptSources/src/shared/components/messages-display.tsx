import * as React from 'react';
import { connect } from 'react-redux';
import {
    IMessage,
} from '../interfaces';

interface IState {
    messages?: IMessage[];
}

class MessagesDisplay extends React.Component<IState, {}> {
    public render() {
        const {messages} = this.props;
        return <>{messages.map((message, index) => {
            return (<div key={index} className={'react-message alert alert-' + message.level}> {message.text}</div>);
        })}</>;
    }
}

interface ISubmitStore {
    submit: IState;
}

const mapStateToProps = (state: ISubmitStore & any): IState => {
    return {
        messages: state.submit.messages,
    };
};
const mapDispatchToProps = (): IState => {
    return {};
};

export default connect(mapStateToProps, mapDispatchToProps)(MessagesDisplay);
