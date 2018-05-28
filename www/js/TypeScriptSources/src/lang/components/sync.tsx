import * as React from 'react';
import { connect } from 'react-redux';
import { Dispatch } from 'redux';
import { loadLang } from '../actions';
import { ILangStore } from '../reducers';

export interface ILanguageDefinition {
    lang: string;
    data: {
        [key: string]: string;
    };
}

interface IProps {
    languagesDefinition: ILanguageDefinition;
}

interface IState {
    onLoad?: (data: ILanguageDefinition) => void;
}

class Async extends React.Component<IState & IProps, {}> {

    public componentDidMount() {
        const {onLoad, languagesDefinition} = this.props;
        onLoad(languagesDefinition);
    }

    public render() {
        return null;
    }

}

const mapDispatchToProps = (dispatch: Dispatch<ILangStore>): IState => {
    return {
        onLoad: (data: ILanguageDefinition) => dispatch(loadLang(data)),
    };
};

const mapStateToProps = (): IState => {
    return {};
};

export default connect(mapStateToProps, mapDispatchToProps)(Async);
