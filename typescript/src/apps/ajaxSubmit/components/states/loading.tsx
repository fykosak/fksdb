import { lang } from '@i18n/i18n';
import * as React from 'react';

export default class Loading extends React.Component<{}, {}> {
    public render() {
        return <div className="text-center">
            <span className="d-block">{lang.getText('Loading')}</span>
            <span className="display-1 d-block"><i className="fa fa-spinner fa-spin "/></span>
        </div>;
    }
}
