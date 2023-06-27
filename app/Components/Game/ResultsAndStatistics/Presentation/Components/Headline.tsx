import Ordinal from '@translator/Ordinal';
import * as React from 'react';
import { TranslatorContext } from '@translator/LangContext';

interface OwnProps {
    startPosition: number;
    endPosition: number;
    category: string;
}

export default class Headline extends React.Component<OwnProps, never> {

    static contextType = TranslatorContext;

    public render() {
        const {category, startPosition, endPosition} = this.props;
        const translator = this.context;
        return (
            <div className="row justify-content-md-center">
                <div className="col-3">
                    <span className="subheader">
                        <Ordinal order={startPosition}/>-<Ordinal order={endPosition}/>
                    </span>
                    <h1>
                        {category ?
                            (translator.getLocalizedText('Category', 'en') + ' ' + category) :
                            translator.getLocalizedText('Global results', 'en')}
                    </h1>
                </div>
                <div className="col-3">
                    <span className="subheader">{startPosition}.-{endPosition}.</span>
                    <h1>
                        {category ?
                            (translator.getLocalizedText('Category', 'cs') + ' ' + category) :
                            translator.getLocalizedText('Global results', 'cs')}
                    </h1>
                </div>
            </div>
        );
    }
}
