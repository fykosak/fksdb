import ActionsStoreCreator from 'vendor/fykosak/nette-frontend-component/src/Components/actions-store-creator';
import { NetteActions } from 'vendor/fykosak/nette-frontend-component/src/NetteActions/nette-actions';
import { SubmitModel } from 'FKSDB/Models/ORM/Models/submit-model';
import * as React from 'react';
import UploadContainer from './Components/container';
import { app, Store } from './Reducers';
import './style.scss';
import { TranslatorContext } from '@translator/context';
import { availableLanguage, Translator } from '@translator/translator';

interface Props {
    data: SubmitModel;
    actions: NetteActions;
    translator: Translator<availableLanguage>;
}

export default function AjaxSubmitComponent({actions, data, translator}: Props) {
    return <ActionsStoreCreator<Store, SubmitModel>
        initialData={{
            actions: actions,
            data: data,
            messages: [],
        }}
        app={app}
    >
        <TranslatorContext.Provider value={translator}>
            <UploadContainer/>
        </TranslatorContext.Provider>
    </ActionsStoreCreator>;
}
