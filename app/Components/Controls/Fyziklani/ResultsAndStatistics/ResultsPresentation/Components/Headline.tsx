import Ordinal from '@translator/Ordinal';
import { translator } from '@translator/translator';
import * as React from 'react';

interface OwnProps {
    startPosition: number;
    endPosition: number;
    category: string;
}

export default class Headline extends React.Component<OwnProps> {

    public render() {
        const {category, startPosition, endPosition} = this.props;

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
