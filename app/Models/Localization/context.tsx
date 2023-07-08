import * as React from 'react';
import { availableLanguage, Translator } from '@translator/translator';

export const TranslatorContext = React.createContext<Translator<availableLanguage>>(null);
