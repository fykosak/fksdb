import {
    LocalizedString,
    translator,
} from '@translator/translator';
import * as React from 'react';

interface OwnProps {
    description: LocalizedString;
}

export default class DescriptionLabel extends React.Component<OwnProps> {

    public render() {
        const {description} = this.props;
        return <span className="description-label ms-3">
            {description[translator.getCurrentLocale()] &&
            <small>{description[translator.getCurrentLocale()]}</small>}
        </span>;
    }
}
