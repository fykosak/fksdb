import Ordinal from '@translator/ordinal';
import * as React from 'react';
import { useContext } from 'react';
import { TranslatorContext } from '@translator/context';

interface OwnProps {
    startPosition: number;
    endPosition: number;
    category: string;
}

export default function Headline({category, startPosition, endPosition}: OwnProps) {
    const translator = useContext(TranslatorContext);
    return <div className="row justify-content-evenly">
        <div className="col-5 d-flex justify-content-center">
            <div>
                <span className="subheader">
                    <Ordinal order={startPosition}/>-<Ordinal order={endPosition}/>
                </span>
                <h1>
                    {category ? 'Category ' + category : 'Global results'}
                </h1>
            </div>
        </div>
        <div className="col-5 d-flex justify-content-center">
            <div>
                <span className="subheader">{startPosition}.-{endPosition}.</span>
                <h1>
                    {category ? 'Kategorie ' + category : 'Globální výsledky'}
                </h1>
            </div>
        </div>
    </div>;
}
