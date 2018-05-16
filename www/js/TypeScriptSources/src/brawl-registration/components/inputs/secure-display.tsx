import * as React from 'react';
import { connect } from 'react-redux';
import { Dispatch } from 'redux';
import { clearProviderProviderProperty } from '../../actions/load';

interface IProps {
    modifiable: boolean;
}

interface IState {
    onClearValue?: () => void;
}

class SecureDisplay extends React.Component<any & IState & IProps, {}> {

    public render() {
        const {modifiable, onClearValue} = this.props;
        return <div className="row">
            <div className={modifiable ? 'col-8' : 'col-12'}>
                <span className="text-success">Tento udaj už v systéme máme uložený,
                                ak ho chcete zmeniť kliknite na tlačítko upraviť</span>
            </div>
            {modifiable && (<div className="col-4">
                <button className="btn btn-warning" onClick={(event) => {
                    event.preventDefault();
                    onClearValue();
                }}>Opraviť hodnotu
                </button>
            </div>)}
        </div>;
    }
}

const mapDispatchToProps = (dispatch: Dispatch<any>, ownProps: any): IState => {
    return {
        onClearValue: () => dispatch(clearProviderProviderProperty(ownProps.input.name)),
    };
};

const mapStateToProps = (): IState => {
    return {};
};

export default connect(mapStateToProps, mapDispatchToProps)(SecureDisplay);
