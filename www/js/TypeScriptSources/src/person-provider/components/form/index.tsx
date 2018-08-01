import * as React from 'react';
import EmailSearch from './email-search';

interface IProps {
    accessKey: string;
}

export default class Form extends React.Component<IProps, {}> {

    public render() {
        let searchInput = null;
        const searchType = 'email';
        switch (searchType) {
            case 'email':
                searchInput = <EmailSearch/>;
                break;
            default:
                throw new Error();
        }
        return <>
            {searchInput}
        </>;
    }
}
