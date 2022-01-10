import { translator } from '@translator/translator';
import * as React from 'react';

export default class LoadingState extends React.Component {
    public render() {
        return <div className="text-center">
            <span className="d-block">{translator.getText('Loading')}</span>
            <span className="display-1 d-block"><i className="fa fa-spinner fa-spin "/></span>
        </div>;
    }
}
