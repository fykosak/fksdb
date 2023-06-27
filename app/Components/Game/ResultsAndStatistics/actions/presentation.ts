export interface Params {
    cols?: number;
    delay?: number;
    position?: number;
    rows?: number;
    category?: string;
    hardVisible?: boolean;
}

export const ACTION_SET_PARAMS = '@@game/presentation/ACTION_SET_PARAMS';
