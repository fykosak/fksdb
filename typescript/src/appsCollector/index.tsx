import { mapRegister } from '@appsCollector/mapRegister';
import * as React from 'react';
import { NetteActions } from './netteActions';

export type App = (element: Element, reactId: string, rawData: string, actions: NetteActions) => boolean;

class AppsCollector {
    private items: App[] = [];

    public register(item: App): void {
        this.items.push(item);
    }

    public run(): void {

        document.querySelectorAll('.react-root,[data-react-root]').forEach((element: Element) => {
            if (element.getAttribute('data-served')) {
                return;
            }
            const reactId = element.getAttribute('data-react-id');
            const rawData = element.getAttribute('data-data');
            const actionsData = JSON.parse(element.getAttribute('data-actions'));
            const actions = new NetteActions(actionsData);

            const selectedItem = this.items.find((item) => {
                return item(element, reactId, rawData, actions);
            });
            if (selectedItem || mapRegister.render(element, reactId, rawData, actions)) {
                element.setAttribute('data-served', '1');
                return;
            }
            throw new Error('no match type');
        });
    }
}

export const appsCollector = new AppsCollector();
