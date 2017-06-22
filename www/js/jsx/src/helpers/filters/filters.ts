export interface IFilter {
    room?: string;
    category?: string;
    name: string;
}
export class Filter implements IFilter {
    public name: string;
    public category: string;
    public room: string;

    constructor({room, category, name}) {
        this.category = category;
        this.room = room;
        this.name = name;
    }

    public match({room = '', category = ''}): boolean {
        if (this.category !== category) {
            return false;
        }
        return !(this.room !== room);
    }
}

export const filters: Array<Filter> = [
    new Filter({room: null, category: null, name: "ALL"}),
    new Filter({room: null, category: 'A', name: "A"}),
    new Filter({room: null, category: 'B', name: "B"}),
    new Filter({room: null, category: 'C', name: "C"}),
    new Filter({room: 'M1', category: null, name: "M1"}),
    new Filter({room: 'M2', category: null, name: "M2"}),
    new Filter({room: 'M3', category: null, name: "M3"}),
    new Filter({room: 'M5', category: null, name: "M5"}),
    new Filter({room: 'F1', category: null, name: "F1"}),
    new Filter({room: 'F2', category: null, name: "F2"}),
    new Filter({room: 'S3', category: null, name: "S3"}),
    new Filter({room: 'S5', category: null, name: "S5"}),
    new Filter({room: 'S9', category: null, name: "S9"}),
];
