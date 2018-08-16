import { fyziklani } from './fyziklani';

class App {

    private components: Array<() => void> = [];

    public register(component: () => void) {
        this.components.push(component);
    }

    public run() {
        this.components.forEach((component) => component());
    }
}

fyziklani();
