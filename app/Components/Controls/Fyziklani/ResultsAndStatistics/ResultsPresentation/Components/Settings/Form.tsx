import { translator } from '@translator/translator';
import * as React from 'react';
import { connect } from 'react-redux';
import HardVisibleSwitch from '../../../Helpers/HardVisible/Component';
import { FyziklaniResultsPresentationStore } from '../../Reducers';
import ColsField from './ColsField';
import DelayField from './DelayField';
import RowsField from './RowsField';

interface StateProps {
    isOrg: boolean;
}

class Form extends React.Component<StateProps, Record<string, never>> {

    public render() {
        const {isOrg} = this.props;
        return <div className="modal fade" id="fyziklaniResultsOptionModal" tabIndex={-1} role="dialog">
            <div className="modal-dialog" role="document">
                <div className="modal-content">
                    <div className="modal-header">
                        <h5 className="modal-title">{translator.getText('Options')}</h5>
                        <button type="button" className="close" data-dismiss="modal">
                            <span>&times;</span>
                        </button>
                    </div>
                    <div className="modal-body">
                        {isOrg && <HardVisibleSwitch/>}
                        <hr/>
                        <DelayField/>
                        <hr/>
                        <ColsField/>
                        <hr/>
                        <RowsField/>
                    </div>
                </div>
            </div>
        </div>;
    }
}

const mapStateToPros = (state: FyziklaniResultsPresentationStore): StateProps => {
    return {
        isOrg: state.options.isOrg,
    };
};

export default connect(mapStateToPros, null)(Form);
