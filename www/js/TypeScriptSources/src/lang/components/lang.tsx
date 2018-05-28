import * as React from 'react';
import { connect } from 'react-redux';
import { ILangStore } from '../reducers';

interface IProps {
    text: string;
}

interface IState {
    isReady?: boolean;
    translation?: string;
}

class LangDisplay extends React.Component<IState & IProps, {}> {

    public render() {
        const {isReady, translation, text} = this.props;
        if (isReady) {
            return <>{translation ? translation : text}</>;
        }

        return <span className="fa fa-spinner fa-spin"/>;
    }

}

const mapDispatchToProps = (): IState => {
    return {};
};

const mapStateToProps = (state: { lang: ILangStore }, ownProps: IProps): IState => {
    if (!state.lang.data.hasOwnProperty(ownProps.text)) {
        console.log(ownProps.text);
    }
    return {
        isReady: state.lang.isReady,
        translation: state.lang.data[ownProps.text],
    };
};

const ConnectedDisplay = connect(mapStateToProps, mapDispatchToProps)(LangDisplay);

export default class Lang extends React.Component<IProps, {}> {

    public render() {
        if (!this.props.text) {
            return null;
        }
        return <ConnectedDisplay text={this.props.text}/>;
    }

}
