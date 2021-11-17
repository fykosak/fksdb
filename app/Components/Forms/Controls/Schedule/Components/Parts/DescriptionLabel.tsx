import {
    LocalizedString,
    translator,
} from '@translator/translator';
import * as React from 'react';

interface OwnProps {
    description: LocalizedString;
}

export default class DescriptionLabel extends React.Component<OwnProps, Record<string, never>> {

    public render() {
        const {description} = this.props;
        return <span className="description-label ml-3">
            {description[translator.getCurrentLocale()] &&
            <small>{description[translator.getCurrentLocale()]}</small>}
        </span>;
    }
}
