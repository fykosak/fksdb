import { lang } from '@i18n/i18n';
import * as React from 'react';
import { connect } from 'react-redux';
import HardVisibleSwitch from '../../../../../options/compoents/hardVisibleSwitch';
import { FyziklaniResultsStore } from '../../../../reducers';
import ColsField from './colsField';
import DelayField from './delayField';
import RowsField from './rowsField';

interface StateProps {
    isOrg: boolean;
}

class Index extends React.Component<StateProps, {}> {

    public render() {
        const {isOrg} = this.props;
        return <div className="modal fade" id="fyziklaniResultsOptionModal" tabIndex={-1} role="dialog">
            <div className="modal-dialog" role="document">
                <div className="modal-content">
                    <div className="modal-header">
                        <h5 className="modal-title">{lang.getText('Options')}</h5>
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

const mapStateToPros = (state: FyziklaniResultsStore): StateProps => {
    return {
        isOrg: state.options.isOrg,
    };
};

export default connect(mapStateToPros, null)(Index);
