import {
    ACTION_SET_ACTIVE_POINTS,
    ACTION_SET_AGGREGATION_TIME,
    ACTION_SET_FIRST_TEAM_ID,
    ACTION_SET_FROM_DATE,
    ACTION_SET_SECOND_TEAM_ID,
    ACTION_SET_TASK_ID,
    ACTION_SET_TO_DATE,
    IActionSetActivePoints,
    IActionSetAggregationTime,
    IActionSetFromDate,
    IActionSetTaskId,
    IActionSetTeamId,
    IActionSetToDate,
} from '../actions/';

export interface IFyziklaniStatisticsState {
    activePoints?: number;
    taskId?: number;
    firstTeamId?: number;
    secondTeamId?: number;
    aggregationTime: number;
    fromDate?: Date;
    toDate?: Date;
}

const setTaskId = (state: IFyziklaniStatisticsState, action: IActionSetTaskId): IFyziklaniStatisticsState => {
    const {taskId} = action;
    return {
        ...state,
        taskId,
    };
};

const setActivePoints = (state: IFyziklaniStatisticsState, action: IActionSetActivePoints): IFyziklaniStatisticsState => {
    const {activePoints} = action;
    return {
        ...state,
        activePoints,
    };
};

const setFirstTeamId = (state: IFyziklaniStatisticsState, action: IActionSetTeamId): IFyziklaniStatisticsState => {
    return {
        ...state,
        firstTeamId: action.teamId,
    };
};

const setSecondTeamId = (state: IFyziklaniStatisticsState, action: IActionSetTeamId): IFyziklaniStatisticsState => {
    return {
        ...state,
        secondTeamId: action.teamId,
    };
};

const setAggregationTime = (state: IFyziklaniStatisticsState, action: IActionSetAggregationTime): IFyziklaniStatisticsState => {
    return {
        ...state,
        aggregationTime: action.time,
    };
};

const setFromDate = (state: IFyziklaniStatisticsState, action: IActionSetFromDate): IFyziklaniStatisticsState => {
    return {
        ...state,
        fromDate: action.from,
    };
};

const setToDate = (state: IFyziklaniStatisticsState, action: IActionSetToDate): IFyziklaniStatisticsState => {
    return {
        ...state,
        toDate: action.to,
    };
};

export const stats = (state: IFyziklaniStatisticsState = {aggregationTime: 5 * 60 * 1000}, action): IFyziklaniStatisticsState => {
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
