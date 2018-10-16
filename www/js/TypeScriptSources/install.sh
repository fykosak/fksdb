#!/bin/bash

cd ./www/js/TypeScriptSources/
npm -g install
webpack --color --mode="production"
cd -
