import * as React from 'react';
import MultiSelect from './multiSelect';
import SingleSelect from './singleSelect';

interface OwnProps {
    mode?: string;
}

export default class Index extends React.Component<OwnProps, {}> {

    public render() {
        const {mode} = this.props;
        /*  if (mode === 'presentation') {
              return <MultiSelect/>;
          }*/
        return <SingleSelect/>;
    }
}
