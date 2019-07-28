import { lang } from '@i18n/i18n';
import Ordinal from '@i18n/ordinal';
import * as React from 'react';

interface Props {
    startPosition: number;
    endPosition: number;
    category: string;
}

export default class Headline extends React.Component<Props, {}> {

    public render() {
        const {category, startPosition, endPosition} = this.props;

        return (
            <h1 className={'text-center row justify-content-center'}>
                <span className={'mr-3'}>
                    <span>{category ?
                        (lang.getLocalizedText('Category', 'en') + ' ' + category) :
                        lang.getLocalizedText('Global results', 'en')} </span>
                    <small className={'text-muted'}><Ordinal order={startPosition}/>-<Ordinal order={endPosition}/></small>
                </span>
                <span className={'ml-3'}>
                    <span>{category ?
                        (lang.getLocalizedText('Category', 'cs') + ' ' + category) :
                        lang.getLocalizedText('Global results', 'cs')} </span>
                    <small className={'text-muted'}>{startPosition}.-{endPosition}.</small>
                    </span>
            </h1>
        );
    }
}
