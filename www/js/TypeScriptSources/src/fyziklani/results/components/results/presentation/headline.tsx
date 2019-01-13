import * as React from 'react';
import { lang } from '../../../../../i18n/i18n';
import Ordinal from '../../../../../i18n/ordinal';

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
                        <span>{category ? ('Category ' + category) : lang.getText('Results of Fyziklani')} </span>
                        <small className={'text-muted'}><Ordinal order={startPosition}/>-<Ordinal order={endPosition}/></small>
                        </span>
                <span className={'ml-3'}>
                        <span>{category ? ('Kategorie ' + category) : lang.getText('Results of Fyziklani')} </span>
                        <small className={'text-muted'}>{startPosition}.-{endPosition}.</small>
                    </span>
            </h1>
        );
    }
}
