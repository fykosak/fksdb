import {
    ACTION_SET_ACTIVE_POINTS,
    ACTION_SET_AGGREGATION_TIME,
    ACTION_SET_FIRST_TEAM_ID,
    ACTION_SET_FROM_DATE,
    ACTION_SET_SECOND_TEAM_ID,
    ACTION_SET_TASK_ID,
    ACTION_SET_TO_DATE,
    ActionSetActivePoints,
    ActionSetAggregationTime,
    ActionSetFromDate,
    ActionSetTaskId,
    ActionSetTeamId,
    ActionSetToDate,
} from '../actions/';

export interface State {
    activePoints?: number;
    taskId?: number;
    firstTeamId?: number;
    secondTeamId?: number;
    aggregationTime: number;
    fromDate?: Date;
    toDate?: Date;
}

const setTaskId = (state: State, action: ActionSetTaskId): State => {
    const {taskId} = action;
    return {
        ...state,
        taskId,
    };
};

const setActivePoints = (state: State, action: ActionSetActivePoints): State => {
    const {activePoints} = action;
    return {
        ...state,
        activePoints,
    };
};

const setFirstTeamId = (state: State, action: ActionSetTeamId): State => {
    return {
        ...state,
        firstTeamId: action.teamId,
    };
};

const setSecondTeamId = (state: State, action: ActionSetTeamId): State => {
    return {
        ...state,
        secondTeamId: action.teamId,
    };
};

const setAggregationTime = (state: State, action: ActionSetAggregationTime): State => {
    return {
        ...state,
        aggregationTime: action.time,
    };
};

const setFromDate = (state: State, action: ActionSetFromDate): State => {
    return {
        ...state,
        fromDate: action.from,
    };
};

const setToDate = (state: State, action: ActionSetToDate): State => {
    return {
        ...state,
        toDate: action.to,
    };
};

export const stats = (state: State = {aggregationTime: 5 * 60 * 1000}, action): State => {
    switch (action.type) {
        case ACTION_SET_ACTIVE_POINTS:
            return setActivePoints(state, action);
        case ACTION_SET_TASK_ID:
            return setTaskId(state, action);
        case ACTION_SET_FIRST_TEAM_ID:
            return setFirstTeamId(state, action);
        case ACTION_SET_SECOND_TEAM_ID:
            return setSecondTeamId(state, action);
        case ACTION_SET_AGGREGATION_TIME:
            return setAggregationTime(state, action);
        case ACTION_SET_FROM_DATE:
            return setFromDate(state, action);
        case ACTION_SET_TO_DATE:
            return setToDate(state, action);
        default:
            return state;
    }
};
