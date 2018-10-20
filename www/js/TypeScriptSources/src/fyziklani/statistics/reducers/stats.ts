import { Action } from 'redux';
import {
    ACTION_SET_ACTIVE_POINTS,
    ACTION_SET_CHART_TYPE,
    ACTION_SET_TASK_ID,
    ACTION_SET_TEAM_ID,
    IActionSetActivePoints,
    IActionSetChartType,
    IActionSetTaskId,
    IActionSetTeamId,
} from '../actions/';

export interface IFyziklaniStatisticsState {
    teamId?: number;
    activePoints?: number;
    chartType?: string;
    taskId?: number;
}

const setTeamId = (state: IFyziklaniStatisticsState, action: IActionSetTeamId): IFyziklaniStatisticsState => {
    const {teamId} = action;
    return {
        ...state,
        teamId,
    };
};

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

const setChartType = (state: IFyziklaniStatisticsState, action: IActionSetChartType): IFyziklaniStatisticsState => {
    const {chartType} = action;
    return {
        ...state,
        chartType,
    };
};

export const stats = (state: IFyziklaniStatisticsState = {}, action): IFyziklaniStatisticsState => {
    switch (action.type) {
        case ACTION_SET_TEAM_ID:
            return setTeamId(state, action);
        case ACTION_SET_ACTIVE_POINTS:
            return setActivePoints(state, action);
        case ACTION_SET_CHART_TYPE:
            return setChartType(state, action);
        case ACTION_SET_TASK_ID:
            return setTaskId(state, action);
        default:
            return state;
    }
};
