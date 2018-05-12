import * as React from 'react';

export default class PersonContainer extends React.Component<any, {}> {
    public render() {
        const {persons} = this.props;
        console.log(persons);
        //  {error && <li className="error">{error}</li>}   <input {...fields.personId.input} type="text" className="form-control"/>
        return <div>
            <div className="form-group">
                <label>
                    <span>Team name</span>

                </label>
            </div>
        </div>;
    }
}
