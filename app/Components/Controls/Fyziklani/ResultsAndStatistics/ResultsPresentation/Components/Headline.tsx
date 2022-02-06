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
            <h1 className="text-center row justify-content-center">
                <span className="me-3">
                    {category ?
                        (translator.getLocalizedText('Category', 'en') + ' ' + category) :
                        translator.getLocalizedText('Global results', 'en')}
                    <small className="text-muted">
                        <Ordinal order={startPosition}/>-<Ordinal order={endPosition}/>
                    </small>
                </span>
                <span className="ms-3">
                    {category ?
                        (translator.getLocalizedText('Category', 'cs') + ' ' + category) :
                        translator.getLocalizedText('Global results', 'cs')}
                    <small className="text-muted">{startPosition}.-{endPosition}.</small>
                    </span>
            </h1>
        );
    }
}
