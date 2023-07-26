import * as React from 'react';
import { useContext } from 'react';
import { TranslatorContext } from '@translator/context';

export default function LoadingState() {

    const translator = useContext(TranslatorContext);
    return <div className="text-center">
        <span className="d-block">{translator.getText('Loading')}</span>
        <span className="display-1 d-block"><i className="fas fa-spinner fa-spin "/></span>
    </div>;
}
