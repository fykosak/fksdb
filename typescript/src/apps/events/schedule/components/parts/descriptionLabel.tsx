import { lang, LocalizedString } from '@i18n/i18n';
import * as React from 'react';

interface OwnProps {
    description: LocalizedString;
}

export default class DescriptionLabel extends React.Component<OwnProps, {}> {

    public render() {
        const {description} = this.props;
        return <span className="description-label">
            {description[lang.getCurrentLocale()] &&
            <small>{description}</small>}
        </span>;
    }
}
