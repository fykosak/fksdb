import { app } from 'FKSDB/Components/Schedule/Input/reducer';
import InputConnectorPrimitive from './input-connector-primitive';
import StoreCreator from 'vendor/fykosak/nette-frontend-component/src/Components/store-creator';
import { ScheduleGroupModel } from 'FKSDB/Models/ORM/Models/Schedule/schedule-group-model';
import * as React from 'react';
import { useEffect } from 'react';
import Group from 'FKSDB/Components/Schedule/Input/Components/group';
import { TranslatorContext } from '@translator/context';
import { Translator } from '@translator/translator';

interface OwnProps {
    scheduleDef: {
        group: ScheduleGroupModel;
    };
    input: HTMLInputElement | HTMLSelectElement;
    translator: Translator;
}

export default function ScheduleField({input, scheduleDef, translator}: OwnProps) {
    const {group} = scheduleDef;
    useEffect(() => {
        input.style.display = 'none';
        input.required = false;
        const label = input.parentElement.getElementsByTagName('label')[0];
        if (label && label instanceof HTMLLabelElement) {
            label.style.display = 'none';
        }
    }, []);

    return <StoreCreator app={app}>
        <TranslatorContext.Provider value={translator}>
            <InputConnectorPrimitive input={input}/>
            <Group group={group}/>
        </TranslatorContext.Provider>
    </StoreCreator>;
}
