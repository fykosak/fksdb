import { ITeam } from '../../../shared/interfaces';

export interface IFilter {
    roomId?: number;
    category?: string;
    name: string;
}

export class Filter implements IFilter {
    public name: string;
    public category: string;
    public roomId: number;

    constructor({roomId, category, name}) {
        this.category = category;
        this.roomId = roomId;
        this.name = name;
    }

    public match(team: ITeam): boolean {
        const {roomId, category} = team;
        if (this.category && this.category !== category) {
            return false;
        }
        return !(this.roomId && this.roomId !== roomId);
    }

    public same(filter: Filter): boolean {
        if (!filter) {
            return false;
        }
        return (filter.roomId === this.roomId) && (filter.category === this.category) && (filter.name === this.name);
    }

    public getHeadline(): string {
        return this.name;
    }
}
