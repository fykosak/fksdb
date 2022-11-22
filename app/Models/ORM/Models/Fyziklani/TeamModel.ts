export interface TeamModel {
    teamId: number;
    category: string;
    name: string;
    status: string;
    created: string;
    gameLang: 'cs' | 'en';
    points: number | null;
    x?: number;
    y?: number;
    roomId?: number;
}
