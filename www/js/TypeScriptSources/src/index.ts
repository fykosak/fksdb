<<<<<<< HEAD
import { fyziklani } from './fyziklani';
=======
import { eventAccommodation } from './events/accommodation';
>>>>>>> fykosak/master

type IApp = (element: Element, module: string, component: string, mode: string, rawData: string) => boolean;

class AppCollector {
    private items: IApp[] = [];

    public register(item: IApp) {
        this.items.push(item);
    }

    public run() {
<<<<<<< HEAD
        document.querySelectorAll('.react-root').forEach((element: Element) => {
=======
        document.querySelectorAll('.react-root,[data-react-root]').forEach((element: Element) => {
>>>>>>> fykosak/master
            const module = element.getAttribute('data-module');
            const component = element.getAttribute('data-component');
            const mode = element.getAttribute('data-mode');
            const rawData = element.getAttribute('data-data');
<<<<<<< HEAD

=======
>>>>>>> fykosak/master
            for (const index in this.items) {
                if (this.items.hasOwnProperty(index)) {
                    const item = this.items[index];
                    if (item(element, module, component, mode, rawData)) {
                        break;
                    }
                }
            }
            throw new Error('no match type');
        });
    }
}

const app = new AppCollector();
<<<<<<< HEAD

app.register(fyziklani);
=======
app.register(eventAccommodation);
>>>>>>> fykosak/master
app.run();
