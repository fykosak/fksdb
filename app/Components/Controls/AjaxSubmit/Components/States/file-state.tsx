import { Store } from 'FKSDB/Components/Controls/AjaxSubmit/Reducers';
import { dispatchNetteFetch } from 'vendor/fykosak/nette-frontend-component/src/fetch/redux/netteFetch';
import { SubmitModel } from 'FKSDB/Models/ORM/Models/submit-model';
import * as React from 'react';
import { useContext } from 'react';
import { useDispatch, useSelector } from 'react-redux';
import { TranslatorContext } from '@translator/context';

interface OwnProps {
    submit: SubmitModel;
}

export default function FileState({submit}: OwnProps) {
    const translator = useContext(TranslatorContext);
    const dispatch = useDispatch();
    const actions = useSelector((state: Store) => state.fetch.actions);
    return <div className="uploaded-file">
        <button aria-hidden="true" className="pull-right btn btn-outline-warning"
                title={translator.getText('Revoke')}
                onClick={() => {
                    if (window.confirm(translator.getText('Remove submit?'))) {
                        dispatchNetteFetch<SubmitModel>(actions.getAction('revoke'), dispatch, JSON.stringify({}))
                    }
                }}>&times;</button>
        <div className="text-center p-2">
            <a href={actions.getAction('download')}>
                <span className="display-1 w-100"><i className="fa fa-file-pdf"/></span>
                <span className="d-block">{translator.get(submit.name)}</span>
            </a>
        </div>
    </div>;
}
