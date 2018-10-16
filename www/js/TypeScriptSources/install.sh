#!/bin/bash

cd ./www/js/TypeScriptSources/
npm install
webpack --color --mode="production"
cd -
