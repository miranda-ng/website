#!/bin/bash

cd /var/lib/python/tasman/ 
source bin/activate
cd src
python tasman/app.py 2>/tmp/talisman.log
