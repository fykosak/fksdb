import { netteFetch } from '../../../../../fetch-api/middleware/fetch';

interface ITeamNameResponse {
    result: boolean;
}

interface ITeamNameRequest {
    name: string;
}

export const asyncValidate = (values, dispatch) => {
    console.log(values);
    return new Promise((resolve) => {

        netteFetch<ITeamNameRequest, ITeamNameResponse>({
            act: 'team-name-unique',
            data: {
                name: values.teamName,
            },
        }, (response) => {
            if (!response.data.result) {
                resolve({teamName: response.messages[0].text});
            }
        }, (e) => {
            throw e;
        });
    });
};
