import { IPlace } from '../../helpers/interfaces';

export interface IRoutingDragNDropData {
    teamId: number;
    place?: IPlace;
}

export interface IResponseData {
    updatedTeams: number[];
}
