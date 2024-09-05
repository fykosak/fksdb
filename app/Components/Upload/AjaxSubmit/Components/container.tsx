import { Store } from 'FKSDB/Components/Upload/AjaxSubmit/Reducers';
import * as React from 'react';
import { useContext } from 'react';
import { useSelector } from 'react-redux';
import MessageBox from './message-box';
import File from './States/file-state';
import Form from './States/form-state';
import LoadingState from './States/loading-state';
import { TranslatorContext } from '@translator/context';
import { Translator } from '@translator/translator';

export default function UploadContainer() {

    const translator = useContext(TranslatorContext);
    const submit = useSelector((state: Store) => state.uploadData.submit);
    const submitting = useSelector((state: Store) => state.fetch.submitting);
    const actions = useSelector((state: Store) => state.fetch.actions);
    const getInnerContainer = (translator: Translator) => {
        if (submit.disabled) {
            return <p className="alert alert-info">{translator.getText('Task is not for your category.')}</p>;
        }
        if (submitting) {
            return <LoadingState/>;
        }
        if (submit.isQuiz) {
            return <a className="btn btn-primary"
                      href={actions.getAction('quiz')}>{translator.getText('Submit using quiz form')}</a>;
        }
        if (submit.submitId) {
            return <File submit={submit}/>;
        } else {
            return <Form/>;
        }
    }
    if (submit === undefined) return null;
    const headline = (<>
        <h4>{translator.get(submit.name)}</h4>
        <small className="text-muted">{submit.deadline}</small>
    </>);
    return <Card headline={headline} level="info">
        <MessageBox/>
        {getInnerContainer(translator)}
    </Card>;
}


export interface OwnProps {
    children?: React.ReactNode;
    headline: string | JSX.Element;
    level: string;
}

function Card({level, headline, children}: OwnProps) {
    return <div className={'card border-' + level}>
        <div className={'card-header card-' + level}>{headline}</div>
        <div className="card-block card-body">
            {children}
        </div>
    </div>;
}
