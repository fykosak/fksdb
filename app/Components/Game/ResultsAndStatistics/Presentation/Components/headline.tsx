import Ordinal from '@translator/ordinal';
import * as React from 'react';
import { useContext } from 'react';
import { TranslatorContext } from '@translator/context';

interface OwnProps {
    startPosition: number;
    endPosition: number;
    category: string;
}

export default function Headline(props: OwnProps) {
    const translator = useContext(TranslatorContext);
    const {category, startPosition, endPosition} = props;
    return <div className="row justify-content-md-center">
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
    </div>;
}
