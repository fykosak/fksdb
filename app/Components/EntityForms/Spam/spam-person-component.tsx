import * as React from 'react';
import StoreCreator from 'vendor/fykosak/nette-frontend-component/src/Components/store-creator';
import { app } from './reducer';
import SpamPersonForm from './spam-person-form';
import { NetteActions } from 'vendor/fykosak/nette-frontend-component/src/NetteActions/nette-actions';
import { Translator } from '@translator/translator';
import { TranslatorContext } from '@translator/context';

interface OwnProps {
    data: {
        studyYears: Map<string, Map<string, string>>
        acYear: number
    }
    actions: NetteActions,
    translator: Translator
}

export default function SpamPersonComponent({data: {studyYears, acYear}, actions, translator}: OwnProps) {
    return <StoreCreator app={app}>
        <TranslatorContext.Provider value={translator}>
            <SpamPersonForm actions={actions} studyYears={studyYears} acYear={acYear}/>
        </TranslatorContext.Provider>
    </StoreCreator>;
}
