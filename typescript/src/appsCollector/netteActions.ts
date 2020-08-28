interface NetteActionsData {
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
