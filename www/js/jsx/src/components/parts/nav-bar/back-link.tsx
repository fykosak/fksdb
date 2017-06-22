import * as React from "react";

interface IProps{

}

export default class BackLink extends React.Component<IProps, void> {
    public render() {
        return (
            <a className="nav-link" onClick={() => {
                return window.history.back();
            }}>
                Späť
            </a>
        );
    }
}
