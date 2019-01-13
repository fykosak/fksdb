export interface NetteActions {
    [name: string]: string;
}

export type App = (element: Element, module: string, component: string, mode: string, rawData: string, actions: NetteActions) => boolean;

class AppCollector {
    private items: App[] = [];

    public register(item: App) {
        this.items.push(item);
    }

    public run() {

        document.querySelectorAll('.react-root,[data-react-root]').forEach((element: Element) => {
            // if (element.className.match(/.*react-element-served.*/)) {
            if (element.getAttribute('data-served')) {
                return;
            }
            const module = element.getAttribute('data-module');
            const component = element.getAttribute('data-component');
            const mode = element.getAttribute('data-mode');
            const rawData = element.getAttribute('data-data');
            const actions = JSON.parse(element.getAttribute('data-actions'));

            for (const index in this.items) {
                if (this.items.hasOwnProperty(index)) {
                    const item = this.items[index];
                    if (item(element, module, component, mode, rawData, actions)) {
                        element.setAttribute('data-served', '1');
                        // element.className += ' react-element-served';
                        return;
                    }
                }
            }
            throw new Error('no match type');
        });
    }
}

export const app = new AppCollector();
