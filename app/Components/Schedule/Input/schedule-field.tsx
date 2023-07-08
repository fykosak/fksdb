import { app } from 'FKSDB/Components/Schedule/Input/reducer';
import InputConnectorPrimitive from './input-connector-primitive';
import StoreCreator from 'vendor/fykosak/nette-frontend-component/src/Components/store-creator';
import { ScheduleGroupModel } from 'FKSDB/Models/ORM/Models/Schedule/schedule-group-model';
import * as React from 'react';
import { useEffect } from 'react';
import Group from 'FKSDB/Components/Schedule/Input/Components/group';
import { TranslatorContext } from '@translator/context';
import { availableLanguage, Translator } from '@translator/translator';

interface OwnProps {
    scheduleDef: {
        group: ScheduleGroupModel;
        options: Params;
    };
    input: HTMLInputElement | HTMLSelectElement;
    translator: Translator<availableLanguage>;
}

export interface Params {
    groupTime: boolean;
    groupLabel: boolean;
    capacity: boolean;
    description: boolean;
    price: boolean;
}

export default function ScheduleField({input, scheduleDef, translator}: OwnProps) {
    const {group, options} = scheduleDef;
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
            {group
                ? <Group group={group} params={options}/>
                : <span className="text-muted">{translator.getText('No items found.')}</span>
            }
        </TranslatorContext.Provider>
    </StoreCreator>;
}
