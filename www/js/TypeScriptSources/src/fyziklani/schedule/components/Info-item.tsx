import * as React from 'react';
import { lang } from '../../../i18n/i18n';
import { ILocalizedInfo } from './index';

interface IProps {
    item: ILocalizedInfo;
    blockName: string;
}

export default class InfoItem extends React.Component<IProps, {}> {

    public render() {
        const {item} = this.props;
        const langKey = lang.getCurrentLocale();
        if (!item.hasOwnProperty(langKey)) {
            return null;
        }
        const localizedData = item[langKey];
        return (

            <div className={'info-container'}>
                <div className={'info-inner-container row'}>
                    <div className={'col-2 info-check-container'}>
                        <span className={'h1 mr-2 fa fa-info'}/>
                    </div>
                    <div className={'col-10'}>
                        <h6>{localizedData.name}</h6>
                        <div>{localizedData.description}</div>
                    </div>
                </div>
            </div>
        );
    }
}
