import * as React from 'react';
import { lang } from '../../../../../../typescript/i18n/i18n';

export default class LoadingState extends React.Component<{}, {}> {
    public render() {
        return <div className="text-center">
            <span className="d-block">{lang.getText('Loading')}</span>
            <span className="display-1 d-block"><i className="fa fa-spinner fa-spin "/></span>
        </div>;
    }
}
