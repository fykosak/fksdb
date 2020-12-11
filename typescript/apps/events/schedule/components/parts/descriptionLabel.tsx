import * as React from 'react';
import {
    LocalizedString,
    translator,
} from '@translator/Translator';

interface OwnProps {
    description: LocalizedString;
}

export default class DescriptionLabel extends React.Component<OwnProps, {}> {

    public render() {
        const {description} = this.props;
        return <span className="description-label ml-3">
            {description[translator.getCurrentLocale()] &&
            <small>{description[translator.getCurrentLocale()]}</small>}
        </span>;
    }
}
