import * as React from 'react';
import { TranslatorContext } from '@translator/LangContext';

export default class LoadingState extends React.Component {
    static contextType = TranslatorContext;
    public render() {
        const translator = this.context;
        return <div className="text-center">
            <span className="d-block">{translator.getText('Loading')}</span>
            <span className="display-1 d-block"><i className="fa fa-spinner fa-spin "/></span>
        </div>;
    }
}
