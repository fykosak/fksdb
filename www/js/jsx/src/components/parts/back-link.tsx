import * as React from "react";

export default class BackLink extends React.Component<void, void> {
    public render() {
        return (
            <button className="btn btn-default" onClick={()=>{return window.history.back();}}>
                <i className="glyphicon glyphicon-chevron-left"/>
            </button>
        );
    }
}
