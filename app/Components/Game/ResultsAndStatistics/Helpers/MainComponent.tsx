import Downloader, { ResponseData } from 'FKSDB/Components/Game/ResultsAndStatistics/Helpers/Downloader/Downloader';
import ActionsStoreCreator from 'vendor/fykosak/nette-frontend-component/src/Components/ActionsStoreCreator';
import { NetteActions } from 'vendor/fykosak/nette-frontend-component/src/NetteActions/netteActions';
import * as React from 'react';
import { Action, Reducer } from 'redux';
import { availableLanguage, Translator } from '@translator/translator';
import { TranslatorContext } from '@translator/LangContext';

interface OwnProps<Store> {
    actions: NetteActions;
    data: ResponseData;
    children: React.ReactNode;
    app: Reducer<Store, Action<string>>;
    translator: Translator<availableLanguage>;
}

export default class MainComponent<Store> extends React.Component<OwnProps<Store>, never> {
    public render() {
        const initialData = {
            actions: this.props.actions,
            data: this.props.data,
            messages: [],
        };
        return <ActionsStoreCreator initialData={initialData} app={this.props.app}>
            <TranslatorContext.Provider value={this.props.translator}>
                <Downloader data={this.props.data}/>
                {this.props.children}
            </TranslatorContext.Provider>
        </ActionsStoreCreator>;
    }
}
