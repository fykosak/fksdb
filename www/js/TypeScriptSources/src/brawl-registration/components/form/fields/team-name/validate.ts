import { Dispatch } from 'redux';
import { dispatchNetteFetch } from '../../../../../fetch-api/middleware/fetch';
import { IStore } from '../../../../reducers';

interface ITeamNameResponse {
    result: boolean;
}

interface ITeamNameRequest {
    name: string;
}

export const asyncValidate = (values, dispatch: Dispatch<IStore>) => {
    return dispatchNetteFetch<ITeamNameRequest, ITeamNameResponse, IStore>('@@brawl-registration/team-name-unique', dispatch, {
        act: 'team-name-unique',
        data: {
            name: values.teamName,
        },
    })
        .then((response) => {
            if (!response.data.result) {
                throw {teamName: response.messages[0].text};
            }
        });
};
