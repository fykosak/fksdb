export interface NetteActionsData {
    [name: string]: string;
}

export class NetteActions {
    private readonly data: NetteActionsData;

    constructor(data: NetteActionsData) {
        this.data = data;
    }

    public getAction(key: string): string {
        return this.data[key];
    }
}

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
            debugger;
            throw new Error('no match type');
        });
    }
}

export const appsCollector = new AppsCollector();

export type mapRegisterCallback = (element: Element, reactId: string, data: string, actions: NetteActions) => void;

class MapRegister {
    private apps: {
        [key: string]: mapRegisterCallback;
    } = {};

    public register(reactId: string, callback: mapRegisterCallback): void {
        if (this.apps.hasOwnProperty(reactId)) {
            throw new Error('App with "' + reactId + '" is already registred.');
        }
        this.apps[reactId] = callback;
    }

    public render(element, reactId, rawData, actions): boolean {
        if (this.apps.hasOwnProperty(reactId)) {
            this.apps[reactId](element, reactId, rawData, actions);
            return true;
        }
        return false;
    }
}

export const mapRegister = new MapRegister();
