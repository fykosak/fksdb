import { Place } from '../../helpers/interfaces';

export interface DragNDropData {
    teamId: number;
    place?: Place;
}

export interface ResponseData {
    updatedTeams: number[];
}
