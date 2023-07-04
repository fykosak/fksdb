#!/usr/bin/env python3

import re
import json
import os
import sys

output_path = './i18n-data.ts'
js_pattern = re.compile('^#: .*\.tsx')

msg_pattern = re.compile('(?P<type>(msgid|msgstr|msgid_plural)(\[(?P<index>\d+)\])?) "(?P<value>.*)"')

def parse_record(record):
    attributes = {}
    for line in record:
        msg_match = msg_pattern.match(line)
        if line == "" or line[0] == "#":
            continue
        elif msg_match:
            type = msg_match.group('type')
            value = msg_match.group('value')
            attributes[type] = value
        # TODO not supported in extract
        #elif line[0] == '"': #multiline strings
        #    res[type] += line[1:-1]
        else:
            raise ValueError("\"{}\" is not a valid record.".format(line))
    if 'msgid_plural' in attributes:
        res = {}
        for index in range(0,3):
            if (f'msgstr[{index}]' in attributes):
                res[index] = attributes[f'msgstr[{index}]']
        return attributes['msgid'], res

    return attributes['msgid'], attributes['msgstr']

def parse(filename):
    res = {}
    with open(filename, 'r') as f:
        inside = False
        for line in f:
            line = line.rstrip('\n')
            if inside:
                record.append(line)
            if js_pattern.search(line):
                record = []
                inside = True
            if inside and line == "":
                key,value = parse_record(record)
                res[key] = value
                inside = False
    return res

def po_to_json():
    # get path of this script
    root_dir = os.path.dirname(os.path.realpath(sys.argv[0]))
    locale_dir = os.path.join(root_dir,'locale')
    messages_path = 'LC_MESSAGES/messages.po'
    langs = [x for x in os.listdir(locale_dir) if os.path.isfile(os.path.join(locale_dir, x, messages_path))]

    res = {}
    for lang in langs:
        res[lang] = parse(os.path.join(locale_dir, lang, messages_path))
    return json.dumps(res, ensure_ascii=False)

def create_ts_file(output_path, json):
    if os.path.isabs(output_path):
        path = output_path
    else:
        root_dir = os.path.dirname(os.path.realpath(sys.argv[0]))
        path = os.path.join(root_dir, output_path)
    with open(path, 'w') as f:
        f.write("/* AUTOMATICALLY GENERATED FROM .po FILES */\n")
        f.write("export const data = ")
        f.write(json)
        f.write(";\n")

json = po_to_json()
create_ts_file(output_path, json)
