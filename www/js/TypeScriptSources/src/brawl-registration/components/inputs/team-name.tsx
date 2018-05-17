import * as React from 'react';

export default class TeamName extends React.Component<any, {}> {
    public render() {
        const {input} = this.props;
        return <div>
            <label>Team name</label>
            <input {...input} type="text"/>
        </div>;
    }
}
