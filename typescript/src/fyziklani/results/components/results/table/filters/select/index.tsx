import * as React from 'react';
import MultiSelect from './multiSelect';
import SingleSelect from './singleSelect';

interface Props {
    mode: string;
}

export default class Index extends React.Component<Props, {}> {

    public render() {
        const {mode} = this.props;
        if (mode === 'presentation') {
            return <MultiSelect/>;
        }
        return <SingleSelect/>;
    }
}
