import * as React from 'react';
import { lang } from '../i18n/i18n';

interface IProps {
    submitId: number;
    label: string;
}

export default class DownloadLink extends React.Component<IProps, {}> {
    private readonly linkFormat = '/submit/download/%d';

    public render() {
        const {submitId, label} = this.props;

        return <div className="col">
            <a
                className="btn btn-secondary btn-sm"
                href={this.linkFormat.replace('%d', '' + submitId)}>
                {lang.getText('Download') + ' ' + label};
            </a>
        </div>;
    }
}
