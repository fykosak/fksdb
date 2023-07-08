import Downloader, { ResponseData } from 'FKSDB/Components/Game/ResultsAndStatistics/Helpers/Downloader/downloader';
import ActionsStoreCreator from 'vendor/fykosak/nette-frontend-component/src/Components/actions-store-creator';
import { NetteActions } from 'vendor/fykosak/nette-frontend-component/src/NetteActions/nette-actions';
import * as React from 'react';
import { Action, Reducer } from 'redux';
import { availableLanguage, Translator } from '@translator/translator';
import { TranslatorContext } from '@translator/context';

interface OwnProps<Store> {
    actions: NetteActions;
    data: ResponseData;
    children: React.ReactNode;
    app: Reducer<Store, Action<string>>;
    translator: Translator<availableLanguage>;
}

export default function MainComponent<Store>({app, actions, data, children, translator}: OwnProps<Store>) {
    const initialData = {
        actions: actions,
        data: data,
        messages: [],
    };
    return <ActionsStoreCreator<Store, ResponseData> initialData={initialData} app={app}>
        <TranslatorContext.Provider value={translator}>
            <Downloader/>
            {children}
        </TranslatorContext.Provider>
    </ActionsStoreCreator>;
}
