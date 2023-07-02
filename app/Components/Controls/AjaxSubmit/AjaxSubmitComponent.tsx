import ActionsStoreCreator from 'vendor/fykosak/nette-frontend-component/src/Components/ActionsStoreCreator';
import { NetteActions } from 'vendor/fykosak/nette-frontend-component/src/NetteActions/netteActions';
import { SubmitModel } from 'FKSDB/Models/ORM/Models/SubmitModel';
import * as React from 'react';
import UploadContainer from './Components/Container';
import { app, Store } from './Reducers';
import './style.scss';
import { TranslatorContext } from '@translator/LangContext';
import { availableLanguage, Translator } from '@translator/translator';

interface Props {
    data: SubmitModel;
    actions: NetteActions;
    translator: Translator<availableLanguage>;
}

export default class AjaxSubmitComponent extends React.Component<Props, never> {

    public render() {
        return <ActionsStoreCreator<Store, SubmitModel>
            initialData={{
                actions: this.props.actions,
                data: this.props.data,
                messages: [],
            }}
            app={app}
        >
            <TranslatorContext.Provider value={this.props.translator}>
                <UploadContainer/>
            </TranslatorContext.Provider>
        </ActionsStoreCreator>;
    }
}
